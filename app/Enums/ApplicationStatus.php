<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'PENDING';
    case RECOMMENDED = 'RECOMMENDED';
    case APPROVED = 'APPROVED';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::RECOMMENDED => 'Recommended',
            self::APPROVED => 'Approved',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::RECOMMENDED => 'blue',
            self::APPROVED => 'green',
            self::FAILED => 'red',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::PENDING => in_array($target, [self::RECOMMENDED, self::FAILED]),
            self::RECOMMENDED => in_array($target, [self::APPROVED, self::PENDING]),
            self::APPROVED => $target === self::RECOMMENDED,
            self::FAILED => $target === self::PENDING,
        };
    }
}
