<?php

namespace App\Enums;

enum JobStatus: string
{
    case OPEN = 'open';
    case MATCHED = 'matched';
    case INVESTIGATING = 'investigating';
    case PREPARING = 'preparing';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::OPEN => in_array($newStatus, [self::MATCHED, self::CANCELLED, self::EXPIRED]),
            self::MATCHED => in_array($newStatus, [self::INVESTIGATING, self::PREPARING, self::IN_PROGRESS, self::CANCELLED]),
            self::INVESTIGATING => in_array($newStatus, [self::PREPARING, self::IN_PROGRESS, self::CANCELLED]),
            self::PREPARING => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED]),
            self::IN_PROGRESS => $newStatus === self::COMPLETED,
            default => false,
        };
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::MATCHED => 'Matched',
            self::INVESTIGATING => 'Investigating',
            self::PREPARING => 'Preparing',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
        };
    }
}
