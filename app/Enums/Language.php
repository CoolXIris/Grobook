<?php

namespace App\Enums;

enum Language: string
{
    case Indonesia = 'indonesia';
    case Inggris = 'inggris';
    case IndonesiaInggris = 'indonesia_inggris';

    public function label(): string
    {
        return match ($this) {
            self::Indonesia => 'Indonesia',
            self::Inggris => 'Inggris',
            self::IndonesiaInggris => 'Indonesia dan Inggris',
        };
    }
}