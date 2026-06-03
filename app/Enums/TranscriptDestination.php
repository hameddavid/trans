<?php

namespace App\Enums;

enum TranscriptDestination: string
{
    case WES = 'WES';
    case NIGERIA = 'NIGERIA';
    case AFRICA = 'AFRICA';
    case AMERICA = 'AMERICA';
    case ASIA = 'ASIA';
    case AUSTRALIA = 'AUSTRALIA';
    case EUROPE = 'EUROPE';
    case CANADA = 'CANADA';
    case DEGREE = 'DEGREE';
    case SOFT = 'SOFT';

    public function amount(): int
    {
        return match($this) {
            self::WES => 12000,
            self::NIGERIA => 12000,
            self::SOFT => 12000,
            self::AFRICA => 20000,
            self::AMERICA => 25000,
            self::ASIA => 25000,
            self::AUSTRALIA => 25000,
            self::EUROPE => 25000,
            self::CANADA => 25000,
            self::DEGREE => 5000,
        };
    }

    public function serviceTypeId(): string
    {
        // TODO: Replace with per-destination service type IDs once Remita activates them
        return config('remita.service_types.wes', '9928138149');
    }

    public function label(): string
    {
        return match($this) {
            self::WES => 'World Education Services',
            self::NIGERIA => 'Nigeria',
            self::AFRICA => 'Africa',
            self::AMERICA => 'America',
            self::ASIA => 'Asia',
            self::AUSTRALIA => 'Australia',
            self::EUROPE => 'Europe',
            self::CANADA => 'Canada',
            self::DEGREE => 'Degree Verification',
            self::SOFT => 'Soft Copy',
        };
    }

    public function description(): string
    {
        $amount = number_format($this->amount());
        return "{$this->label()} (₦{$amount})";
    }

    public static function transcriptDestinations(): array
    {
        return array_filter(self::cases(), fn($case) => !in_array($case, [self::DEGREE, self::SOFT]));
    }
}
