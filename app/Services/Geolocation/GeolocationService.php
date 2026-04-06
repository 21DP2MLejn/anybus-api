<?php

namespace App\Services\Geolocation;

use App\Events\DriverLocationUpdated;
use App\Events\DriverNearby;
use App\Models\Job;
use App\Models\Worker;
use App\Models\WorkerLocationHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeolocationService
{
    /**
     * Default average speed in km/h for ETA calculations.
     */
    private const DEFAULT_AVG_SPEED_KMH = 40;

    /**
     * Update worker's current location and store in history.
     */
    public function updateWorkerLocation(
        Worker $worker,
        float $latitude,
        float $longitude,
        ?int $jobId = null
    ): void {
        $now = now();

        // Update worker's current location (always update current)
        $worker->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_location_at' => $now,
        ]);

        // Also update the PostGIS location column
        DB::statement(
            'UPDATE workers SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
            [$longitude, $latitude, $worker->id]
        );

        // Throttle location history logging to every 30 seconds
        $lastHistory = WorkerLocationHistory::where('worker_id', $worker->id)
            ->where('job_id', $jobId)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (! $lastHistory || $now->diffInSeconds($lastHistory->recorded_at) >= 30) {
            WorkerLocationHistory::create([
                'worker_id' => $worker->id,
                'job_id' => $jobId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location' => DB::raw("ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326)::geography"),
                'recorded_at' => $now,
            ]);
        }

        // If there's an active job, verify worker is assigned and broadcast (every 5s via throttle)
        if ($jobId) {
            $job = Job::find($jobId);
            if ($job && $job->accepted_worker_id === $worker->id) {
                $this->broadcastLocationUpdate($worker, $job, $latitude, $longitude);
            }
        }
    }

    /**
     * Calculate distance between two points in kilometers using PostGIS.
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $result = DB::selectOne(
            'SELECT ST_Distance(
                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
            ) / 1000 AS distance_km',
            [$lng1, $lat1, $lng2, $lat2]
        );

        return (float) ($result->distance_km ?? 0);
    }

    /**
     * Calculate distance between worker and job location.
     */
    public function calculateWorkerToJobDistance(Worker $worker, Job $job): ?float
    {
        if (! $worker->latitude || ! $worker->longitude || ! $job->latitude || ! $job->longitude) {
            return null;
        }

        return $this->calculateDistance(
            $worker->latitude,
            $worker->longitude,
            $job->latitude,
            $job->longitude
        );
    }

    /**
     * Calculate ETA in minutes based on distance and average speed.
     */
    public function calculateETA(
        float $distanceKm,
        float $avgSpeedKmh = self::DEFAULT_AVG_SPEED_KMH
    ): int {
        if ($distanceKm <= 0 || $avgSpeedKmh <= 0) {
            return 0;
        }

        return (int) ceil(($distanceKm / $avgSpeedKmh) * 60);
    }

    /**
     * Calculate ETA from worker to job in minutes.
     */
    public function calculateWorkerToJobETA(
        Worker $worker,
        Job $job,
        float $avgSpeedKmh = self::DEFAULT_AVG_SPEED_KMH
    ): ?int {
        $distance = $this->calculateWorkerToJobDistance($worker, $job);

        if ($distance === null) {
            return null;
        }

        return $this->calculateETA($distance, $avgSpeedKmh);
    }

    /**
     * Get location history for a specific job.
     */
    public function getRouteHistory(int $jobId): Collection
    {
        return WorkerLocationHistory::forJob($jobId)
            ->chronological()
            ->get(['latitude', 'longitude', 'recorded_at']);
    }

    /**
     * Get the current location data for a worker assigned to a job.
     */
    public function getDriverLocationForJob(Job $job): ?array
    {
        $worker = $job->acceptedWorker;

        if (! $worker || ! $worker->latitude || ! $worker->longitude) {
            return null;
        }

        $distance = $this->calculateWorkerToJobDistance($worker, $job);
        $eta = $distance !== null ? $this->calculateETA($distance) : null;

        return [
            'latitude' => (float) $worker->latitude,
            'longitude' => (float) $worker->longitude,
            'distance_km' => $distance !== null ? round($distance, 2) : null,
            'eta_minutes' => $eta,
            'last_updated_at' => $worker->last_location_at?->toIso8601String(),
        ];
    }

    /**
     * Broadcast location update to job channel.
     */
    private function broadcastLocationUpdate(
        Worker $worker,
        Job $job,
        float $latitude,
        float $longitude
    ): void {
        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            (float) $job->latitude,
            (float) $job->longitude
        );

        $eta = $this->calculateETA($distance);

        event(new DriverLocationUpdated(
            jobId: $job->id,
            latitude: $latitude,
            longitude: $longitude,
            distanceKm: round($distance, 2),
            etaMinutes: $eta
        ));

        // Proximity alert if within 2km
        if ($distance <= 2.0) {
            event(new DriverNearby(
                jobId: $job->id,
                distanceKm: round($distance, 2)
            ));
        }
    }
}
