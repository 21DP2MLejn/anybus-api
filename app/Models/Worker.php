<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rating',
        'availability_status',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'availability_status' => 'string',
        ];
    }

    /**
     * Get the user that owns the worker profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all skills for this worker.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(WorkerSkill::class);
    }

    /**
     * Get all jobs accepted by this worker.
     */
    public function acceptedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'accepted_worker_id');
    }

    /**
     * Scope a query to only include online workers.
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('availability_status', 'online');
    }

    /**
     * Scope a query to only include available workers.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability_status', 'online');
    }

    /**
     * Scope a query to eager load skills.
     */
    public function scopeWithSkills(Builder $query): Builder
    {
        return $query->with('skills');
    }

    /**
     * Scope a query to filter workers within a radius (using PostGIS).
     */
    public function scopeWithinRadius(Builder $query, float $latitude, float $longitude, float $radiusKm = 10): Builder
    {
        $radiusMeters = $radiusKm * 1000;
        $point = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography';

        return $query->whereRaw("ST_DWithin(location, $point, ?)", [$longitude, $latitude, $radiusMeters]);
    }

    /**
     * Check if worker is online.
     */
    public function isOnline(): bool
    {
        return $this->availability_status === 'online';
    }

    /**
     * Add a skill to the worker.
     */
    public function addSkill(string $skill): WorkerSkill
    {
        return $this->skills()->firstOrCreate(['skill' => $skill]);
    }

    /**
     * Check if worker has a specific skill.
     */
    public function hasSkill(string $skill): bool
    {
        return $this->skills()->where('skill', $skill)->exists();
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
