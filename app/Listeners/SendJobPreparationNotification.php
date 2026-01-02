<?php

namespace App\Listeners;

use App\Events\JobPreparationStarted;
use App\Notifications\JobPreparationStarted as JobPreparationStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobPreparationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobPreparationStarted $event): void
    {
        // Notify the customer that the worker has started preparing
        $event->job->customer->notify(new JobPreparationStartedNotification($event->job));
    }
}
