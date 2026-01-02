<?php

namespace App\Listeners;

use App\Events\JobCancelled;
use App\Notifications\JobCancelled as JobCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobCancelledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobCancelled $event): void
    {
        // Notify the customer that the job was cancelled
        $event->job->customer->notify(new JobCancelledNotification($event->job));

        // Notify the worker if they had accepted the job
        if ($event->job->acceptedWorker && $event->job->acceptedWorker->user_id !== $event->user->id) {
            $event->job->acceptedWorker->user->notify(new JobCancelledNotification($event->job));
        }
    }
}
