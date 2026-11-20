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
            self::Diajukan, self::ReviewDls, self::ReviewAkhir => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
            self::RevisiKetuaTim, self::RevisiDls, self::RevisiKetuaDls => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
            self::Selesai => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
        };
    }

    public function isRevisi(): bool
    {
        return in_array($this, [self::RevisiKetuaTim, self::RevisiDls, self::RevisiKetuaDls], true);
    }
}