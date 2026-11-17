<?php

namespace App\Enums;

enum TimelineAction: string
{
    case Submitted = 'submitted';
    case ContentApproved = 'content_approved';
    case RevisionRequested = 'revision_requested';
    case Resubmitted = 'resubmitted';
    case ForwardedToFinal = 'forwarded_to_final';
    case FinalApproved = 'final_approved';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Naskah diajukan',
            self::ContentApproved => 'Disetujui Ketua Tim',
            self::RevisionRequested => 'Revisi diminta',
            self::Resubmitted => 'Naskah diajukan ulang',
            self::ForwardedToFinal => 'Diteruskan ke pengecekan akhir',
            self::FinalApproved => 'Publikasi disetujui (selesai)',
        };
    }
}