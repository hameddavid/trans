<?php

namespace App\Enums;

enum TranscriptType: string
{
    case OFFICIAL = 'OFFICIAL';
    case STUDENT = 'STUDENT';
    case PROFICIENCY = 'PROFICIENCY';

    public function label(): string
    {
        return match($this) {
            self::OFFICIAL => 'Official Transcript',
            self::STUDENT => 'Student Copy',
            self::PROFICIENCY => 'Proficiency Letter',
        };
    }
}
