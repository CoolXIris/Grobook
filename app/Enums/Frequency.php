<?php

namespace App\Enums;

enum Frequency: string
{
    case EmpatBulanan = 'empat_bulanan';
    case Semesteran = 'semesteran';
    case Tahunan = 'tahunan';
    case TigaTahunan = 'tiga_tahunan';
    case SepuluhTahunan = 'sepuluh_tahunan';
    case Khusus = 'khusus';

    public function label(): string
    {
        return match ($this) {
            self::EmpatBulanan => '4 Bulanan',
            self::Semesteran => 'Semesteran',
            self::Tahunan => 'Tahunan',
            self::TigaTahunan => '3 Tahunan',
            self::SepuluhTahunan => '10 Tahunan',
            self::Khusus => 'Khusus/Ad Hoc/Lainnya',
        };
    }
}