<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerLocationHistory extends Model
{
    use HasFactory;

    protected $table = 'worker_location_history';

    protected $fillable = [
        'worker_id',
        'job_id',
        'latitude',
        'longitude',
        'location',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the worker that owns this location history entry.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the job associated with this location entry (if any).
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope to filter by job.
     */
    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope to filter by worker.
     */
    public function scopeForWorker(Builder $query, int $workerId): Builder
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Scope to order by recorded time.
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('recorded_at', 'asc');
    }
}
