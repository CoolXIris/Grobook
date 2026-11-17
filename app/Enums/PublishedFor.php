<?php

namespace App\Enums;

enum PublishedFor: string
{
    case Internal = 'internal';
    case InternalExternal = 'internal_external';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal',
            self::InternalExternal => 'Internal dan External',
        };
    }
}