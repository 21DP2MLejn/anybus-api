<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerSkill extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'worker_id',
        'skill',
    ];

    /**
     * Get the primary key for the model.
     */
    public function getKeyName()
    {
        return null;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey()
    {
        return null;
    }

    /**
     * Get the worker that owns the skill.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
