<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Diajukan = 'diajukan';
    case RevisiKetuaTim = 'revisi_ketua_tim';
    case ReviewDls = 'review_dls';
    case RevisiDls = 'revisi_dls';
    case ReviewAkhir = 'review_akhir';
    case RevisiKetuaDls = 'revisi_ketua_dls';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Menunggu Review Ketua Tim',
            self::RevisiKetuaTim => 'Revisi dari Ketua Tim',
            self::ReviewDls => 'Menunggu Review Petugas DLS',
            self::RevisiDls => 'Revisi dari Petugas DLS',
            self::ReviewAkhir => 'Menunggu Pengecekan Akhir',
            self::RevisiKetuaDls => 'Revisi dari Ketua DLS',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Diajukan, self::ReviewDls, self::ReviewAkhir => 'bg-amber-100 text-amber-800',
            self::RevisiKetuaTim, self::RevisiDls, self::RevisiKetuaDls => 'bg-red-100 text-red-800',
            self::Selesai => 'bg-green-100 text-green-800',
        };
    }

    public function isRevisi(): bool
    {
        return in_array($this, [self::RevisiKetuaTim, self::RevisiDls, self::RevisiKetuaDls], true);
    }
}