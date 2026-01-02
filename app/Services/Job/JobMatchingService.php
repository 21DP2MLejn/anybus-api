<?php

namespace App\Services\Job;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\Worker;
use Illuminate\Support\Collection;

class JobMatchingService
{
    /**
     * Match jobs to a worker based on distance, skills, and price.
     */
    public function matchJobsToWorker(Worker $worker, int $limit = 20): Collection
    {
        $radius = 10; // default 10km

        if (! $worker->latitude || ! $worker->longitude) {
            return collect([]);
        }

        $radiusMeters = $radius * 1000;

        $jobs = Job::query()
            ->where('status', JobStatus::OPEN->value)
            ->whereDoesntHave('interactions', function ($q) use ($worker) {
                $q->where('worker_id', $worker->id);
            })
            ->whereNotNull('location')
            ->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$worker->longitude, $worker->latitude, $radiusMeters]
            )
            ->selectRaw(
                '*, ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) as distance_meters',
                [$worker->longitude, $worker->latitude]
            )
            ->get();

        return $jobs->map(function ($job) use ($worker) {
            $score = $this->calculateJobScore($job, $worker);
            $job->match_score = $score;

            return $job;
        })
            ->sortByDesc('match_score')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate match score for a job.
     */
    private function calculateJobScore(Job $job, Worker $worker): float
    {
        $score = 0.0;

        // Distance score using PostGIS distance (already calculated in query)
        if (isset($job->distance_meters)) {
            $distanceKm = $job->distance_meters / 1000;
            // Inverse distance score (max 10km = 100 points, closer = more points)
            $score += max(0, 100 - ($distanceKm * 10));
        }

        // Skill match score (if job category matches worker skills)
        if ($worker->hasSkill($job->category)) {
            $score += 50;
        }

        // Price score (higher price = higher score, but diminishing returns)
        $score += min(50, $job->price / 10);

        return $score;
    }
}
