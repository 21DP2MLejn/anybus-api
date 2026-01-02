<?php

namespace App\Listeners;

use App\Events\JobStarted;
use App\Notifications\JobStarted as JobStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobStartedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobStarted $event): void
    {
        // Notify the customer that the worker has started the job
        $event->job->customer->notify(new JobStartedNotification($event->job));
    }
}
