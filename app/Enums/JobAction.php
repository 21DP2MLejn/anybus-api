<?php

namespace App\Enums;

enum JobAction: string
{
    case ACCEPT_JOB = 'accept_job';
    case START_INVESTIGATION = 'start_investigation';
    case START_PREPARATION = 'start_preparation';
    case START_JOB = 'start_job';
    case COMPLETE_JOB = 'complete_job';
    case CANCEL_JOB = 'cancel_job';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ACCEPT_JOB => 'Accept Job',
            self::START_INVESTIGATION => 'Start Investigation',
            self::START_PREPARATION => 'Start Preparation',
            self::START_JOB => 'Start Job',
            self::COMPLETE_JOB => 'Complete Job',
            self::CANCEL_JOB => 'Cancel Job',
        };
    }

    public function requiresComment(): bool
    {
        return match ($this) {
            self::ACCEPT_JOB => false,
            self::START_INVESTIGATION => true,
            self::START_PREPARATION => true,
            self::START_JOB => true,
            self::COMPLETE_JOB => true,
            self::CANCEL_JOB => true,
        };
    }

    public function getMinCommentLength(): int
    {
        return $this->requiresComment() ? 30 : 0;
    }
}
