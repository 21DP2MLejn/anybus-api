<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobCompleted;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Models\Job;
use App\Models\Worker;

class CompleteJobAction
{
    /**
     * Complete a job.
     */
    public function execute(Job $job, Worker $worker): Job
    {
        // Validate job can be completed
        if ($job->status !== JobStatus::IN_PROGRESS) {
            throw new InvalidJobStatusException('Job must be in progress before it can be completed.');
        }

        if ($job->accepted_worker_id !== $worker->id) {
            throw new UnauthorizedJobActionException('Only the accepted worker can complete this job.');
        }

        // Update job status
        $job->status = JobStatus::COMPLETED;
        $job->save();

        // Dispatch event
        event(new JobCompleted($job, $worker->user));

        return $job;
    }
}
