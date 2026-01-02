<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobInteraction;
use App\Models\Worker;
use Illuminate\Validation\ValidationException;

class SwipeJobService
{
    /**
     * Record a swipe action on a job.
     */
    public function swipe(Job $job, Worker $worker, string $action): JobInteraction
    {
        // Validate action
        if (! in_array($action, ['liked', 'skipped'])) {
            throw ValidationException::withMessages([
                'action' => ['Invalid action. Must be "liked" or "skipped".'],
            ]);
        }

        // Check if interaction already exists
        $existingInteraction = JobInteraction::where('job_id', $job->id)
            ->where('worker_id', $worker->id)
            ->first();

        if ($existingInteraction) {
            // Update existing interaction
            $existingInteraction->action = $action;
            $existingInteraction->save();

            return $existingInteraction;
        }

        // Create new interaction
        return JobInteraction::create([
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'action' => $action,
            'created_at' => now(),
        ]);
    }
}
