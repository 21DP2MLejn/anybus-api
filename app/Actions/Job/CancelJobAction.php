<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobCancelled;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Models\Job;
use App\Models\User;

class CancelJobAction
{
    /**
     * Cancel a job.
     */
    public function execute(Job $job, User $user): Job
    {
        // Validate job can be cancelled
        if (! in_array($job->status, [JobStatus::OPEN, JobStatus::MATCHED])) {
            throw new InvalidJobStatusException('Job can only be cancelled when open or matched.');
        }

        // Validate user has permission to cancel
        $isCustomer = $job->customer_id === $user->id;
        $isAcceptedWorker = $job->accepted_worker_id
            && $job->acceptedWorker
            && $job->acceptedWorker->user_id === $user->id;

        if (! $isCustomer && ! $isAcceptedWorker) {
            throw new UnauthorizedJobActionException('Only the customer or accepted worker can cancel this job.');
        }

        // Update job status
        $job->status = JobStatus::CANCELLED;
        $job->save();

        // Dispatch event
        event(new JobCancelled($job, $user));

        return $job;
    }
}
