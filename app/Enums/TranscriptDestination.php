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

    public function serviceTypeId(): string
    {
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

    public static function transcriptDestinations(): array
    {
        return array_filter(self::cases(), fn($case) => !in_array($case, [self::DEGREE, self::SOFT]));
    }
}
