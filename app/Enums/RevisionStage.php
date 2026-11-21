<?php

namespace App\Enums;

enum RevisionStage: string
{
    case KetuaTim = 'ketua_tim';
    case Dls = 'dls';
    case KetuaDls = 'ketua_dls';

    public function label(): string
    {
        return match ($this) {
            self::KetuaTim => 'Ketua Tim',
            self::Dls => 'Petugas DLS',
            self::KetuaDls => 'Ketua DLS',
        };
    }

    public function returnStatus(): PublicationStatus
    {
        return match ($this) {
            self::KetuaTim => PublicationStatus::Diajukan,
            self::Dls, self::KetuaDls => PublicationStatus::ReviewDls,
        };
    }
}