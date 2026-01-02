<?php

namespace App\DTO\Job;

class SwipeJobDTO
{
    public function __construct(
        public readonly int $job_id,
        public readonly int $worker_id,
        public readonly string $action,
    ) {}

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        if (! in_array($data['action'], ['liked', 'skipped'])) {
            throw new \InvalidArgumentException('Action must be "liked" or "skipped".');
        }

        return new self(
            job_id: $data['job_id'],
            worker_id: $data['worker_id'],
            action: $data['action'],
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'job_id' => $this->job_id,
            'worker_id' => $this->worker_id,
            'action' => $this->action,
        ];
    }
}
