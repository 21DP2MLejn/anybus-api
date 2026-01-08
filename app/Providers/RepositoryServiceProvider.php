<?php

namespace App\Providers;

use App\Commands\JobTransitionCommand;
use App\Policies\JobStateTransitionPolicy;
use App\Repositories\User\EloquentUserProfileRepository;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\User\EloquentUserSettingRepository;
use App\Repositories\User\UserProfileRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserSettingRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(
            UserProfileRepositoryInterface::class,
            EloquentUserProfileRepository::class
        );

        $this->app->bind(
            UserSettingRepositoryInterface::class,
            EloquentUserSettingRepository::class
        );

        // Job transition system
        $this->app->singleton(JobTransitionCommand::class, function ($app) {
            return new JobTransitionCommand(
                $app->make(JobStateTransitionPolicy::class)
            );
        });

        $this->app->singleton(JobStateTransitionPolicy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
