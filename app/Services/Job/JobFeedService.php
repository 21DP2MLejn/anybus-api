<?php

namespace App\Services\Job;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\Worker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobFeedService
{
    /**
     * Get job feed for a worker.
     */
    public function getFeedForWorker(Worker $worker, array $filters = []): LengthAwarePaginator
    {
        $radius = $filters['radius'] ?? 10; // default 10km
        $category = $filters['category'] ?? null;

        if (! $worker->latitude || ! $worker->longitude) {
            return Job::query()
                ->where('status', JobStatus::OPEN->value)
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 20);
        }

        $radiusMeters = $radius * 1000;

        $query = Job::query()
            ->where('status', JobStatus::OPEN->value)
            ->whereDoesntHave('interactions', function ($q) use ($worker) {
                $q->where('worker_id', $worker->id);
            })
            ->whereNotNull('location')
            ->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$worker->longitude, $worker->latitude, $radiusMeters]
            );

        // Filter by category if provided
        if ($category) {
            $query->where('category', $category);
        }

        // Order by distance using PostGIS ST_Distance
        $query->selectRaw(
            '*, ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) as distance_meters',
            [$worker->longitude, $worker->latitude]
        )
            ->orderBy('distance_meters');

        return $query->paginate($filters['per_page'] ?? 20);
    }
}
