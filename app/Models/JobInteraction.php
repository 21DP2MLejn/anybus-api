<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInteraction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job_id',
        'worker_id',
        'action',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => 'string',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the job that was interacted with.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * Get the worker who made the interaction.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Scope a query to only include liked interactions.
     */
    public function scopeLiked(Builder $query): Builder
    {
        return $query->where('action', 'liked');
    }

    /**
     * Scope a query to only include skipped interactions.
     */
    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where('action', 'skipped');
    }

    /**
     * Scope a query to filter interactions for a specific worker.
     */
    public function scopeForWorker(Builder $query, Worker $worker): Builder
    {
        return $query->where('worker_id', $worker->id);
    }
}
