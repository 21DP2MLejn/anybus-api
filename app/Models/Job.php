<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_postings';

    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'category',
        'price',
        'latitude',
        'longitude',
        'status',
        'accepted_worker_id',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'status' => JobStatus::class,
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * Get the customer that owns the job.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the worker who accepted the job.
     */
    public function acceptedWorker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'accepted_worker_id');
    }

    /**
     * Get all interactions for this job.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(JobInteraction::class, 'job_id');
    }

    /**
     * Scope a query to only include open jobs.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', JobStatus::OPEN->value);
    }

    /**
     * Scope a query to filter jobs that haven't been interacted with by a worker.
     */
    public function scopeForWorker(Builder $query, Worker $worker): Builder
    {
        return $query->whereDoesntHave('interactions', function ($q) use ($worker) {
            $q->where('worker_id', $worker->id);
        });
    }

    /**
     * Scope a query to filter jobs within a radius (using PostGIS).
     */
    public function scopeWithinRadius(Builder $query, float $latitude, float $longitude, float $radiusKm = 10): Builder
    {
        $radiusMeters = $radiusKm * 1000;
        $point = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography';

        return $query->whereRaw("ST_DWithin(location, $point, ?)", [$longitude, $latitude, $radiusMeters]);
    }

    /**
     * Check if job is open.
     */
    public function isOpen(): bool
    {
        return $this->status === JobStatus::OPEN;
    }

    /**
     * Check if job can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->isOpen() && $this->accepted_worker_id === null;
    }

    /**
     * Get location attribute (PostGIS geography).
     */
    public function getLocationAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ];
        }

        return null;
    }
}
