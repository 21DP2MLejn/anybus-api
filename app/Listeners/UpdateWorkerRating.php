<?php

namespace App\Listeners;

use App\Events\JobCompleted;
use App\Models\Worker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class UpdateWorkerRating implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * Updates worker rating based on completed jobs.
     * Note: For a production system, you should implement a job_reviews table
     * with customer ratings (1-5 stars) and calculate the average from actual reviews.
     */
    public function handle(JobCompleted $event): void
    {
        $worker = $event->job->acceptedWorker;

        if (! $worker) {
            return;
        }

        // TODO: Replace this with actual rating calculation from job_reviews table
        // Example implementation when reviews table exists:
        // $averageRating = DB::table('job_reviews')
        //     ->where('worker_id', $worker->id)
        //     ->whereNotNull('rating')
        //     ->avg('rating');
        //
        // if ($averageRating !== null) {
        //     $worker->update(['rating' => round($averageRating, 2)]);
        // }

        // Current implementation: Calculate rating based on completed jobs and success rate
        $completedJobsCount = $worker->acceptedJobs()
            ->where('status', 'completed')
            ->count();

        $totalAcceptedJobs = $worker->acceptedJobs()
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        // Calculate success rate (completed vs cancelled)
        $successRate = $totalAcceptedJobs > 0
            ? ($completedJobsCount / $totalAcceptedJobs)
            : 0;

        // Base rating starts at 4.0, increases with completed jobs and success rate
        $baseRating = 4.0;
        $completedJobsBonus = min(0.5, $completedJobsCount * 0.02); // Max 0.5 bonus
        $successRateBonus = $successRate * 0.5; // Up to 0.5 bonus for 100% success rate
        $maxRating = 5.0;

        $newRating = min($maxRating, $baseRating + $completedJobsBonus + $successRateBonus);

        // Only update if rating changed significantly (avoid unnecessary updates)
        if (abs($worker->rating - $newRating) >= 0.01) {
            $worker->update(['rating' => round($newRating, 2)]);
        }
    }
}
