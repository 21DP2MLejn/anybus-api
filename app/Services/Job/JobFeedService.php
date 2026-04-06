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
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortBy = $filters['sort_by'] ?? 'distance'; // distance, price, created_at
        $sortOrder = $filters['sort_order'] ?? 'asc';

        $query = Job::query()
            ->where('status', JobStatus::OPEN->value)
            ->whereDoesntHave('interactions', function ($q) use ($worker) {
                $q->where('worker_id', $worker->id);
            })
            ->whereNotNull('location');

        // Filter by radius if worker has location
        if ($worker->latitude && $worker->longitude) {
            $radiusMeters = $radius * 1000;
            $query->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$worker->longitude, $worker->latitude, $radiusMeters]
            );

            $query->selectRaw(
                '*, ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) as distance_meters',
                [$worker->longitude, $worker->latitude]
            );
        }

        // Filter by category if provided
        if ($category) {
            $query->where('category', $category);
        }

        // Filter by price
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        // Sorting
        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'distance':
            default:
                if ($worker->latitude && $worker->longitude) {
                    $query->orderBy('distance_meters', $sortOrder);
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }
}
