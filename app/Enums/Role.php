<?php

namespace App\Enums;

enum Role: string
{
    case TimNaskah = 'tim_naskah';
    case KetuaTim = 'ketua_tim';
    case PetugasDls = 'petugas_dls';
    case KetuaDls = 'ketua_dls';

    public function label(): string
    {
        return match ($this) {
            self::TimNaskah => 'Tim Pembuat Naskah',
            self::KetuaTim => 'Ketua Tim',
            self::PetugasDls => 'Petugas DLS',
            self::KetuaDls => 'Ketua DLS',
        };
    }
}