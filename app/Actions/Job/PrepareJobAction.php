<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobPreparationStarted;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Models\Job;
use App\Models\Worker;

class PrepareJobAction
{
    /**
     * Start preparing for a job.
     */
    public function execute(Job $job, Worker $worker): Job
    {
        // Validate job can be prepared
        if ($job->status !== JobStatus::INVESTIGATING) {
            throw new InvalidJobStatusException('Job must be in investigation phase before preparation can start.');
        }

        if ($job->accepted_worker_id !== $worker->id) {
            throw new UnauthorizedJobActionException('Only the accepted worker can prepare for this job.');
        }

        // Update job status
        $job->status = JobStatus::PREPARING;
        $job->save();

        // Dispatch event
        event(new JobPreparationStarted($job, $worker->user));

        return $job;
    }
}
