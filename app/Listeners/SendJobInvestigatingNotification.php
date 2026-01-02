<?php

namespace App\Listeners;

use App\Events\JobInvestigatingStarted;
use App\Notifications\JobInvestigatingStarted as JobInvestigatingStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJobInvestigatingNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobInvestigatingStarted $event): void
    {
        // Notify the customer that the worker has started investigating
        $event->job->customer->notify(new JobInvestigatingStartedNotification($event->job));
    }
}
