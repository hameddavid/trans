<?php

namespace App\Enums;

enum AdminRole: string
{
    case RECOMMENDER = '200';
    case APPROVER = '300';

    public function label(): string
    {
        return match($this) {
            self::RECOMMENDER => 'Recommender',
            self::APPROVER => 'Approver',
        };
    }

    public function canRecommend(): bool
    {
        return $this === self::RECOMMENDER;
    }

    public function canApprove(): bool
    {
        return $this === self::APPROVER;
    }
}
