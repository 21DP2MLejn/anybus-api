<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name ?? 'Worker ' . $this->id,
            'email' => $this->user->email,
            'rating' => $this->rating,
            'availability_status' => $this->availability_status,
            'total_jobs' => $this->whenLoaded('acceptedJobs', function () {
                return $this->acceptedJobs->count();
            }) ?? $this->acceptedJobs()->count(),
            'skills' => $this->whenLoaded('skills', function () {
                return $this->skills->pluck('skill');
            }),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
