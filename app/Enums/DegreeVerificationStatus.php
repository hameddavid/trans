<?php

namespace App\Enums;

enum DegreeVerificationStatus: string
{
    case PENDING = 'PENDING';
    case TREATED = 'TREATED';
    case RECOMMENDED = 'RECOMMENDED';
    case APPROVED = 'APPROVED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::TREATED => 'Treated',
            self::RECOMMENDED => 'Recommended',
            self::APPROVED => 'Approved',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::TREATED => 'blue',
            self::RECOMMENDED => 'indigo',
            self::APPROVED => 'green',
        };
    }
}
