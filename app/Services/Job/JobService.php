<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobService
{
    /**
     * Get jobs for a user (customer or worker).
     */
    public function getUserJobs(User $user): LengthAwarePaginator
    {
        if ($user->hasRole('customer')) {
            return Job::where('customer_id', $user->id)->paginate();
        }

        if ($user->isWorker() && $user->worker) {
            return Job::where('accepted_worker_id', $user->worker->id)->paginate();
        }

        return Job::whereRaw('1 = 0')->paginate(); // Empty result
    }
}
