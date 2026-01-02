<?php

namespace App\Actions\Job;

use App\Enums\JobStatus;
use App\Events\JobAccepted;
use App\Exceptions\JobAlreadyInteractedException;
use App\Exceptions\JobNotAvailableException;
use App\Exceptions\WorkerNotOnlineException;
use App\Models\Job;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;

class AcceptJobAction
{
    /**
     * Accept a job for a worker.
     */
    public function execute(Job $job, Worker $worker): Job
    {
        return DB::transaction(function () use ($job, $worker) {
            // Lock the job row to prevent race conditions
            $job = Job::lockForUpdate()->findOrFail($job->id);

            // Validate job can be accepted
            if ($job->status !== JobStatus::OPEN) {
                throw new JobNotAvailableException('Job is not open for acceptance.');
            }

            if ($job->accepted_worker_id !== null) {
                throw new JobNotAvailableException('Job has already been accepted by another worker.');
            }

            if (! $worker->isOnline()) {
                throw new WorkerNotOnlineException('Worker must be online to accept jobs.');
            }

            // Check if worker has skipped this job (only skipped interactions prevent acceptance)
            $existingInteraction = $job->interactions()->where('worker_id', $worker->id)->first();
            if ($existingInteraction && $existingInteraction->action === 'skipped') {
                throw new JobAlreadyInteractedException('Worker has skipped this job and cannot accept it.');
            }

            // Update job status
            $job->status = JobStatus::MATCHED;
            $job->accepted_worker_id = $worker->id;
            $job->accepted_at = now();
            $job->save();

            // Dispatch event
            event(new JobAccepted($job, $worker->user));

            return $job;
        });
    }
}
