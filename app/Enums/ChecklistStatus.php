<?php

namespace App\Enums;

enum ChecklistStatus: string
{
    case Sesuai = 'sesuai';
    case TidakSesuai = 'tidak_sesuai';
    case TidakRelevan = 'tidak_relevan';

    public function label(): string
    {
        return match ($this) {
            self::Sesuai => 'Sesuai',
            self::TidakSesuai => 'Tidak Sesuai',
            self::TidakRelevan => 'Tidak Relevan',
        };
    }
}