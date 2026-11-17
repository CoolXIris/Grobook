<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Enums\Role;
use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Publication $publication): bool
    {
        return match ($user->role) {
            Role::TimNaskah => $publication->user_id === $user->id,
            Role::KetuaTim => $publication->team_id === $user->team_id,
            Role::PetugasDls, Role::KetuaDls => true,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === Role::TimNaskah && $user->team_id !== null;
    }

    public function update(User $user, Publication $publication): bool
    {
        return $publication->user_id === $user->id && $publication->status->isRevisi();
    }

    public function reviewContent(User $user, Publication $publication): bool
    {
        return $user->role === Role::KetuaTim
            && $user->team_id !== null
            && $publication->team_id === $user->team_id
            && $publication->status === PublicationStatus::Diajukan;
    }

    public function reviewDls(User $user, Publication $publication): bool
    {
        return $user->role === Role::PetugasDls && $publication->status === PublicationStatus::ReviewDls;
    }

    public function reviewFinal(User $user, Publication $publication): bool
    {
        return $user->role === Role::KetuaDls && $publication->status === PublicationStatus::ReviewAkhir;
    }

    public function requestRevision(User $user, Publication $publication): bool
    {
        return match ($publication->status) {
            PublicationStatus::Diajukan => $this->reviewContent($user, $publication),
            PublicationStatus::ReviewDls => $this->reviewDls($user, $publication),
            PublicationStatus::ReviewAkhir => $this->reviewFinal($user, $publication),
            default => false,
        };
    }

    public function downloadDocuments(User $user, Publication $publication): bool
    {
        return $user->hasRole(Role::PetugasDls, Role::KetuaDls)
            && $publication->status === PublicationStatus::Selesai;
    }
}