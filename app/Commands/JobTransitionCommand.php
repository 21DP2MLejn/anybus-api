<?php

namespace App\Commands;

use App\Enums\JobAction;
use App\Events\JobAccepted;
use App\Events\JobCancelled;
use App\Events\JobCompleted;
use App\Events\JobInvestigatingStarted;
use App\Events\JobPreparationStarted;
use App\Events\JobStarted;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\JobAlreadyInteractedException;
use App\Exceptions\JobNotAvailableException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Exceptions\WorkerNotOnlineException;
use App\Models\Job;
use App\Models\JobEvent;
use App\Models\User;
use App\Policies\JobStateTransitionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobTransitionCommand
{
    public function __construct(
        private readonly JobStateTransitionPolicy $policy
    ) {}

    /**
     * Execute a job transition.
     */
    public function execute(
        Job $job,
        User $actor,
        JobAction $action,
        ?string $comment = null,
        ?array $metadata = null
    ): Job {
        return DB::transaction(function () use ($job, $actor, $action, $comment, $metadata) {
            // Lock the job for update to prevent race conditions
            $job = Job::lockForUpdate()->findOrFail($job->id);

            // Validate the transition
            $this->validateTransition($job, $actor, $action, $comment);

            // Get the expected next state
            $nextState = $this->policy->getExpectedNextState($action);
            if (! $nextState) {
                throw new InvalidJobStatusException("Invalid action: {$action->value}");
            }

            // Perform action-specific validations
            $this->performActionValidations($job, $actor, $action);

            // Create the job event
            $event = JobEvent::create([
                'job_id' => $job->id,
                'actor_user_id' => $actor->id,
                'action' => $action->value,
                'from_state' => $job->status->value,
                'to_state' => $nextState->value,
                'comment' => $comment,
                'metadata' => $metadata,
            ]);

            // Update job status (derived from latest event)
            $job->status = $nextState;
            if ($action === JobAction::ACCEPT_JOB) {
                $job->accepted_worker_id = $actor->worker->id;
                $job->accepted_at = now();
            }
            $job->save();

            // Emit domain events
            $this->emitDomainEvents($job, $actor, $action);

            return $job;
        });
    }

    /**
     * Validate the transition according to policy rules.
     */
    private function validateTransition(Job $job, User $actor, JobAction $action, ?string $comment): void
    {
        // Check if user can perform this action
        if (! $this->policy->canPerformAction($actor, $job, $action)) {
            throw new UnauthorizedJobActionException("User is not authorized to perform action: {$action->value}");
        }

        // Check job ownership
        if (! $this->policy->validateJobOwnership($actor, $job, $action)) {
            throw new UnauthorizedJobActionException('User does not have permission to perform this action on this job');
        }

        // Validate comment requirements
        if ($action->requiresComment()) {
            if (! $comment || strlen($comment) < $action->getMinCommentLength()) {
                throw ValidationException::withMessages([
                    'comment' => "Comment is required and must be at least {$action->getMinCommentLength()} characters long.",
                ]);
            }
        }
    }

    /**
     * Perform action-specific business validations.
     */
    private function performActionValidations(Job $job, User $actor, JobAction $action): void
    {
        switch ($action) {
            case JobAction::ACCEPT_JOB:
                $this->validateAcceptJob($job, $actor);
                break;
            case JobAction::START_INVESTIGATION:
            case JobAction::START_PREPARATION:
            case JobAction::START_JOB:
            case JobAction::COMPLETE_JOB:
                $this->validateWorkerJob($job, $actor);
                break;
        }
    }

    /**
     * Validate job acceptance.
     */
    private function validateAcceptJob(Job $job, User $actor): void
    {
        if ($job->accepted_worker_id !== null) {
            throw new JobNotAvailableException('Job has already been accepted by another worker.');
        }

        if (! $actor->worker?->isOnline()) {
            throw new WorkerNotOnlineException('Worker must be online to accept jobs.');
        }

        // Check if worker has skipped this job
        $existingInteraction = $job->interactions()->where('worker_id', $actor->worker->id)->first();
        if ($existingInteraction && $existingInteraction->action === 'skipped') {
            throw new JobAlreadyInteractedException('Worker has skipped this job and cannot accept it.');
        }
    }

    /**
     * Validate worker job actions.
     */
    private function validateWorkerJob(Job $job, User $actor): void
    {
        if ($job->accepted_worker_id !== $actor->worker?->id) {
            throw new UnauthorizedJobActionException('Only the assigned worker can perform this action.');
        }
    }

    /**
     * Emit domain events for notifications and tracking.
     */
    private function emitDomainEvents(Job $job, User $actor, JobAction $action): void
    {
        switch ($action) {
            case JobAction::ACCEPT_JOB:
                event(new JobAccepted($job, $actor));
                break;
            case JobAction::START_INVESTIGATION:
                event(new JobInvestigatingStarted($job, $actor));
                break;
            case JobAction::START_PREPARATION:
                event(new JobPreparationStarted($job, $actor));
                break;
            case JobAction::START_JOB:
                event(new JobStarted($job, $actor));
                break;
            case JobAction::COMPLETE_JOB:
                event(new JobCompleted($job, $actor));
                break;
            case JobAction::CANCEL_JOB:
                event(new JobCancelled($job, $actor));
                break;
        }
    }
}
