<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobInvestigatingStarted;
use App\Exceptions\InvalidJobStatusException;
use App\Exceptions\UnauthorizedJobActionException;
use App\Models\Job;
use App\Models\Worker;

class InvestigateJobAction
{
    /**
     * Start investigating a job.
     */
    public function execute(Job $job, Worker $worker): Job
    {
        // Validate job can be investigated
        if ($job->status !== JobStatus::MATCHED) {
            throw new InvalidJobStatusException('Job must be matched before investigation can start.');
        }

        if ($job->accepted_worker_id !== $worker->id) {
            throw new UnauthorizedJobActionException('Only the accepted worker can investigate this job.');
        }

        // Update job status
        $job->status = JobStatus::INVESTIGATING;
        $job->save();

        // Dispatch event
        event(new JobInvestigatingStarted($job, $worker->user));

        return $job;
    }
}
