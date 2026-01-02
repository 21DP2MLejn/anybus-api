<?php

namespace App\Listeners;

use App\Events\JobCompleted;
use App\Notifications\JobCompleted as JobCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobCompletedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobCompleted $event): void
    {
        // Notify the customer that the job has been completed
        $event->job->customer->notify(new JobCompletedNotification($event->job));

        // Notify the worker that they completed the job
        $event->worker->notify(new JobCompletedNotification($event->job));
    }
}
