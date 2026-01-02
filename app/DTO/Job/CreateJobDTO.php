<?php

namespace App\DTO\Job;

class CreateJobDTO
{
    public function __construct(
        public readonly int $customer_id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $category,
        public readonly float $price,
        public readonly float $latitude,
        public readonly float $longitude,
    ) {}

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: $data['customer_id'],
            title: $data['title'],
            description: $data['description'],
            category: $data['category'],
            price: (float) $data['price'],
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
