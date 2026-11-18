<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Enums\Role;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $recent = Publication::visibleTo($user)
            ->with(['user', 'team'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        [$counts, $actionItems, $actionLabel, $actionRoute] = match ($user->role) {
            Role::TimNaskah => [
                [
                    'Total Pengajuan' => Publication::visibleTo($user)->count(),
                    'Dalam Proses' => Publication::visibleTo($user)->withStatus(PublicationStatus::Diajukan, PublicationStatus::ReviewDls, PublicationStatus::ReviewAkhir)->count(),
                    'Perlu Revisi' => Publication::visibleTo($user)->withStatus(PublicationStatus::RevisiKetuaTim, PublicationStatus::RevisiDls, PublicationStatus::RevisiKetuaDls)->count(),
                    'Selesai' => Publication::visibleTo($user)->withStatus(PublicationStatus::Selesai)->count(),
                ],
                Publication::visibleTo($user)->withStatus(PublicationStatus::RevisiKetuaTim, PublicationStatus::RevisiDls, PublicationStatus::RevisiKetuaDls)->with('openRevision')->latest()->limit(5)->get(),
                'Naskah yang perlu Anda perbaiki',
                'publications.index',
            ],
            Role::KetuaTim => [
                [
                    'Perlu Pemeriksaan Konten' => Publication::where('team_id', $user->team_id)->withStatus(PublicationStatus::Diajukan)->count(),
                    'Anggota Tim' => User::where('team_id', $user->team_id)->where('role', Role::TimNaskah)->count(),
                    'Publikasi Tim' => Publication::where('team_id', $user->team_id)->count(),
                    'Selesai' => Publication::where('team_id', $user->team_id)->withStatus(PublicationStatus::Selesai)->count(),
                ],
                Publication::where('team_id', $user->team_id)->withStatus(PublicationStatus::Diajukan)->with('user')->latest()->limit(5)->get(),
                'Naskah yang menunggu pemeriksaan konten',
                'reviews.content.index',
            ],
            Role::PetugasDls => [
                [
                    'Perlu Review Layout' => Publication::withStatus(PublicationStatus::ReviewDls)->count(),
                    'Menunggu Ketua DLS' => Publication::withStatus(PublicationStatus::ReviewAkhir)->count(),
                    'Siap Diunduh' => Publication::withStatus(PublicationStatus::Selesai)->count(),
                    'Total Publikasi' => Publication::count(),
                ],
                Publication::withStatus(PublicationStatus::ReviewDls)->with(['user', 'team'])->latest()->limit(5)->get(),
                'Naskah yang menunggu review layout & non-konten',
                'reviews.dls.index',
            ],
            Role::KetuaDls => [
                [
                    'Perlu Pengecekan Akhir' => Publication::withStatus(PublicationStatus::ReviewAkhir)->count(),
                    'Selesai' => Publication::withStatus(PublicationStatus::Selesai)->count(),
                    'Total Publikasi' => Publication::count(),
                    'Total Akun' => User::count(),
                ],
                Publication::withStatus(PublicationStatus::ReviewAkhir)->with(['user', 'team'])->latest()->limit(5)->get(),
                'Naskah yang menunggu pengecekan akhir',
                'reviews.final.index',
            ],
        };

        return view('dashboard', [
            'counts' => $counts,
            'actionItems' => $actionItems,
            'actionLabel' => $actionLabel,
            'actionRoute' => $actionRoute,
            'recent' => $recent,
        ]);
    }
}
