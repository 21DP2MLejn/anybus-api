<?php

namespace App\Policies;

use App\Enums\JobAction;
use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\User;

class JobStateTransitionPolicy
{
    /**
     * Define allowed actions per role.
     */
    private const ROLE_PERMISSIONS = [
        'customer' => [
            JobAction::CANCEL_JOB,
        ],
        'worker' => [
            JobAction::ACCEPT_JOB,
            JobAction::START_INVESTIGATION,
            JobAction::START_PREPARATION,
            JobAction::START_JOB,
            JobAction::COMPLETE_JOB,
            JobAction::CANCEL_JOB,
        ],
        'admin' => [
            JobAction::ACCEPT_JOB,
            JobAction::START_INVESTIGATION,
            JobAction::START_PREPARATION,
            JobAction::START_JOB,
            JobAction::COMPLETE_JOB,
            JobAction::CANCEL_JOB,
        ],
    ];

    /**
     * Define allowed state transitions per action.
     */
    private const STATE_TRANSITIONS = [
        JobAction::ACCEPT_JOB->value => [
            'from' => [JobStatus::OPEN],
            'to' => JobStatus::MATCHED,
        ],
        JobAction::START_INVESTIGATION->value => [
            'from' => [JobStatus::MATCHED],
            'to' => JobStatus::INVESTIGATING,
        ],
        JobAction::START_PREPARATION->value => [
            'from' => [JobStatus::MATCHED, JobStatus::INVESTIGATING],
            'to' => JobStatus::PREPARING,
        ],
        JobAction::START_JOB->value => [
            'from' => [JobStatus::MATCHED, JobStatus::INVESTIGATING, JobStatus::PREPARING],
            'to' => JobStatus::IN_PROGRESS,
        ],
        JobAction::COMPLETE_JOB->value => [
            'from' => [JobStatus::IN_PROGRESS],
            'to' => JobStatus::COMPLETED,
        ],
        JobAction::CANCEL_JOB->value => [
            'from' => [JobStatus::OPEN, JobStatus::MATCHED, JobStatus::INVESTIGATING, JobStatus::PREPARING, JobStatus::IN_PROGRESS],
            'to' => JobStatus::CANCELLED,
        ],
    ];

    /**
     * Check if a user can perform an action on a job.
     */
    public function canPerformAction(User $user, Job $job, JobAction $action): bool
    {
        $userRole = $this->getUserRole($user, $job);

        // Check if the user's role is allowed to perform this action
        if (! in_array($action, self::ROLE_PERMISSIONS[$userRole] ?? [])) {
            return false;
        }

        // Check if the current job state allows this transition
        $transitionRules = self::STATE_TRANSITIONS[$action->value] ?? null;
        if (! $transitionRules) {
            return false;
        }

        return in_array($job->status, $transitionRules['from']);
    }

    /**
     * Get the expected next state for an action.
     */
    public function getExpectedNextState(JobAction $action): ?JobStatus
    {
        return self::STATE_TRANSITIONS[$action->value]['to'] ?? null;
    }

    /**
     * Get allowed actions for a user on a specific job.
     */
    public function getAllowedActions(User $user, Job $job): array
    {
        $userRole = $this->getUserRole($user, $job);
        $allowedActions = [];

        foreach (self::ROLE_PERMISSIONS[$userRole] ?? [] as $action) {
            if ($this->canPerformAction($user, $job, $action)) {
                $allowedActions[] = $action;
            }
        }

        return $allowedActions;
    }

    /**
     * Validate that the user owns the job (for worker actions).
     */
    public function validateJobOwnership(User $user, Job $job, JobAction $action): bool
    {
        // Workers can only perform actions on jobs they've been assigned to
        if (in_array($action, [JobAction::START_INVESTIGATION, JobAction::START_PREPARATION, JobAction::START_JOB, JobAction::COMPLETE_JOB])) {
            return $job->accepted_worker_id === $user->worker?->id;
        }

        // Customers can only cancel their own jobs
        if ($action === JobAction::CANCEL_JOB && ! $user->worker) {
            return $job->customer_id === $user->id;
        }

        return true;
    }

    /**
     * Get the user's role in relation to this job.
     */
    private function getUserRole(User $user, Job $job): string
    {
        if ($user->hasRole('admin')) {
            return 'admin';
        }

        if ($user->worker) {
            return 'worker';
        }

        return 'customer';
    }

    /**
     * Get all allowed transitions from a given state.
     */
    public function getAllowedTransitionsFromState(JobStatus $currentState): array
    {
        $allowedTransitions = [];

        foreach (self::STATE_TRANSITIONS as $action => $rules) {
            if (in_array($currentState, $rules['from'])) {
                $allowedTransitions[$action] = $rules['to'];
            }
        }

        return $allowedTransitions;
    }
}
