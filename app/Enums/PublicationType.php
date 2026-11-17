<?php

namespace App\Enums;

enum PublicationType: string
{
    case Arc = 'arc';
    case NonArc = 'non_arc';

    public function label(): string
    {
        return match ($this) {
            self::Arc => 'ARC',
            self::NonArc => 'NON ARC',
        };
    }
}