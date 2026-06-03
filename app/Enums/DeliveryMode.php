<?php

namespace App\Enums;

enum DeliveryMode: string
{
    case SOFT = 'soft';
    case HARD = 'hard';
    case WES = 'wes';
    case PORTAL = 'portal';

    public function label(): string
    {
        return match($this) {
            self::SOFT => 'Soft Copy (Email)',
            self::HARD => 'Hard Copy',
            self::WES => 'WES',
            self::PORTAL => 'Portal Upload',
        };
    }

    public function requiresAddress(): bool
    {
        return in_array($this, [self::HARD, self::WES, self::PORTAL]);
    }
}
