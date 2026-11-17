<?php

namespace App\Enums;

enum CoverMaker: string
{
    case SubditPublikasi = 'subdit_publikasi';
    case Ipds = 'ipds';
    case SubjectMatter = 'subject_matter';

    public function label(): string
    {
        return match ($this) {
            self::SubditPublikasi => 'Subdit Publikasi',
            self::Ipds => 'IPDS',
            self::SubjectMatter => 'Subject Matter',
        };
    }
}