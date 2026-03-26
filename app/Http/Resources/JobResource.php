<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'status' => $this->status->value,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'customer' => new UserResource($this->whenLoaded('customer')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];

        // Include accepted_worker if job has one assigned (for worker ads)
        if ($this->accepted_worker_id) {
            $data['accepted_worker'] = new WorkerResource($this->whenLoaded('acceptedWorker'));
            $data['accepted_at'] = $this->accepted_at?->toISOString();
        }

        return $data;
    }
}
