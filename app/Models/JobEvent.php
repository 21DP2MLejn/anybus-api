<?php

namespace App\Models;

use App\Enums\JobAction;
use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobEvent extends Model
{
    protected $fillable = [
        'job_id',
        'actor_user_id',
        'action',
        'from_state',
        'to_state',
        'comment',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'action' => JobAction::class,
            'from_state' => JobStatus::class,
            'to_state' => JobStatus::class,
            'metadata' => 'array',
        ];
    }

    /**
     * Get the job that owns this event.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * Get the user who performed this action.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * Scope to get the latest event for each job.
     */
    public function scopeLatestPerJob($query)
    {
        return $query->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY job_id ORDER BY created_at DESC) as rn')
            ->having('rn', '=', 1);
    }

    /**
     * Get the transition summary.
     */
    public function getTransitionSummary(): string
    {
        return "{$this->from_state->getDisplayName()} → {$this->to_state->getDisplayName()}";
    }
}
