<?php

use App\Models\Job;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Job Channel
|--------------------------------------------------------------------------
|
| This channel is used for real-time driver location updates.
| Only the job's customer can subscribe to receive location updates.
|
*/
Broadcast::channel('job.{jobId}', function ($user, int $jobId) {
    $job = Job::find($jobId);

    if (! $job) {
        return false;
    }

    // Only the customer can subscribe to job location updates
    return $job->customer_id === $user->id;
});
