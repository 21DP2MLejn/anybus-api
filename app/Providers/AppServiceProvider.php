<?php

namespace App\Providers;

use App\Events\JobAccepted;
use App\Events\JobCancelled;
use App\Events\JobCompleted;
use App\Events\JobStarted;
use App\Listeners\SendJobAcceptedNotification;
use App\Listeners\SendJobCancelledNotification;
use App\Listeners\SendJobCompletedNotification;
use App\Listeners\SendJobStartedNotification;
use App\Listeners\UpdateWorkerRating;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(JobAccepted::class, SendJobAcceptedNotification::class);
        Event::listen(JobInvestigatingStarted::class, SendJobInvestigatingNotification::class);
        Event::listen(JobPreparationStarted::class, SendJobPreparationNotification::class);
        Event::listen(JobStarted::class, SendJobStartedNotification::class);
        Event::listen(JobCompleted::class, SendJobCompletedNotification::class);
        Event::listen(JobCompleted::class, UpdateWorkerRating::class);
        Event::listen(JobCancelled::class, SendJobCancelledNotification::class);
    }
}
