<?php

namespace App\Listeners;

use App\Events\JobAccepted;
use App\Notifications\JobAccepted as JobAcceptedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobAcceptedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobAccepted $event): void
    {
        // Notify the customer that their job was accepted
        $event->job->customer->notify(new JobAcceptedNotification($event->job));

        // Notify the worker that they successfully accepted the job
        $event->worker->notify(new JobAcceptedNotification($event->job));
    }
}
