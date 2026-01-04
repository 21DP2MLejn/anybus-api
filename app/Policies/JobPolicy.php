<?php

namespace App\Policies;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    /**
     * Determine if the user can accept the job.
     */
    public function accept(User $user, Job $job): bool
    {
        return $user->isWorker()
            && $job->status === JobStatus::OPEN;
    }

    /**
     * Determine if the user can start the job.
     */
    public function start(User $user, Job $job): bool
    {
        return $user->isWorker()
            && $user->worker
            && $job->status === JobStatus::MATCHED
            && $job->accepted_worker_id === $user->worker->id;
    }

    /**
     * Determine if the user can complete the job.
     */
    public function complete(User $user, Job $job): bool
    {
        return $user->isWorker()
            && $user->worker
            && $job->status === JobStatus::IN_PROGRESS
            && $job->accepted_worker_id === $user->worker->id;
    }

    /**
     * Determine if the user can cancel the job.
     */
    public function cancel(User $user, Job $job): bool
    {
        $isCustomer = $job->customer_id === $user->id;
        $isAcceptedWorker = $user->isWorker()
            && $user->worker
            && $job->accepted_worker_id === $user->worker->id;

        return ($isCustomer || $isAcceptedWorker)
            && in_array($job->status, [JobStatus::OPEN, JobStatus::MATCHED]);
    }

    /**
     * Determine if the user can view the job.
     */
    public function view(User $user, Job $job): bool
    {
        $isCustomer = $job->customer_id === $user->id;
        $isAcceptedWorker = $user->isWorker()
            && $user->worker
            && $job->accepted_worker_id === $user->worker->id;
        $isAdmin = $user->hasRole('admin');

        return $isCustomer || $isAcceptedWorker || $isAdmin;
    }

    /**
     * Determine if the user can swipe on the job.
     */
    public function swipe(User $user, Job $job): bool
    {
        return $user->isWorker()
            && $job->status === JobStatus::OPEN;
    }
}
