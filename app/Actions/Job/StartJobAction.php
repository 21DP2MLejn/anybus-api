<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobStarted;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Models\Job;
use App\Models\Worker;

class StartJobAction
{
    /**
     * Start a job (mark as in progress).
     */
    public function execute(Job $job, Worker $worker): Job
    {
        // Validate job can be started
        if (! in_array($job->status, [JobStatus::MATCHED, JobStatus::INVESTIGATING, JobStatus::PREPARING])) {
            throw new InvalidJobStatusException('Job must be matched, investigating, or in preparation phase before it can be started.');
        }

        if ($job->accepted_worker_id !== $worker->id) {
            throw new UnauthorizedJobActionException('Only the accepted worker can start this job.');
        }

        // Update job status
        $job->status = JobStatus::IN_PROGRESS;
        $job->save();

        // Dispatch event
        event(new JobStarted($job, $worker->user));

        return $job;
    }
}
