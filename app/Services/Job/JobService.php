<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class JobService
{
    /**
     * Get all public jobs/advertisements.
     */
    public function getAllJobs(?User $viewer = null): LengthAwarePaginator
    {
        $query = Job::with('customer', 'acceptedWorker.user', 'acceptedWorker.acceptedJobs')
            ->orderBy('created_at', 'desc')
            ;

        // Role-based visibility:
        // - customer users see only worker-made advertisements
        // - worker users see only customer-made advertisements
        if ($viewer?->hasRole(User::ROLE_CUSTOMER)) {
            if (Schema::hasColumn('job_postings', 'ad_type')) {
                $query->where('ad_type', 'worker');
            } else {
                // Backward-compatible fallback: worker ads created via `storeWorkerAd`
                // have customer_id = workers.user_id AND accepted_worker_id = workers.id.
                $query->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('workers')
                        ->whereColumn('workers.user_id', 'job_postings.customer_id')
                        ->whereColumn('workers.id', 'job_postings.accepted_worker_id');
                });
            }
        } elseif ($viewer?->isWorker()) {
            if (Schema::hasColumn('job_postings', 'ad_type')) {
                $query->where('ad_type', 'customer');
            } else {
                // Everything that's NOT a worker ad (per the heuristic) is treated as customer-made.
                $query->whereNotExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('workers')
                        ->whereColumn('workers.user_id', 'job_postings.customer_id')
                        ->whereColumn('workers.id', 'job_postings.accepted_worker_id');
                });
            }
        }

        return $query->paginate();
    }

    /**
     * Get jobs for a user (customer or worker).
     */
    public function getUserJobs(User $user): LengthAwarePaginator
    {
        if ($user->hasRole('customer')) {
            return Job::where('customer_id', $user->id)
                ->with('customer')
                ->paginate();
        }

        if ($user->isWorker() && $user->worker) {
            return Job::where('accepted_worker_id', $user->worker->id)->paginate();
        }

        return Job::whereRaw('1 = 0')->paginate(); // Empty result
    }
}
