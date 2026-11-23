<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\TimelineAction;
use App\Models\ChecklistSection;
use App\Models\QualityCard;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class KartuKendaliGenerator
{
    public function __construct(private DocumentStorage $documentStorage) {}

    public function generate(QualityCard $card): string
    {
        $card->load([
            'publication.user',
            'publication.team',
            'publication.timelineEvents',
            'ketuaTim',
            'answers.checklistItem',
        ]);

        $publication = $card->publication;
        $answersByItem = $card->answers->keyBy('checklist_item_id');

        $sections = ChecklistSection::orderBy('position')
            ->with(['items' => fn ($query) => $query
                ->whereIn('id', $answersByItem->keys())
                ->orderBy('position')
                ->orderBy('id')])
            ->get()
            ->filter(fn (ChecklistSection $section) => $section->items->isNotEmpty());

        $forwardedAt = $publication->timelineEvents
            ->where('action', TimelineAction::ForwardedToFinal)
            ->last()?->created_at;

        $petugasNames = User::where('role', Role::PetugasDls)->orderBy('name')->pluck('name');
        $ketuaDlsNames = User::where('role', Role::KetuaDls)->orderBy('name')->pluck('name');

        $progres = [
            [
                'uraian' => 'Penyusun Naskah Publikasi',
                'nama' => collect([$publication->user->name]),
                'diterima' => $publication->submitted_at,
                'selesai' => $publication->submitted_at,
            ],
            [
                'uraian' => 'Ketua Tim',
                'nama' => collect([$card->ketuaTim->name]),
                'diterima' => $publication->submitted_at,
                'selesai' => $card->approved_at,
            ],
            [
                'uraian' => 'Tim Layanan Internal Diseminasi dan Layanan Statistik',
                'nama' => $petugasNames,
                'diterima' => $card->approved_at,
                'selesai' => $forwardedAt,
            ],
            [
                'uraian' => 'Ketua Tim Diseminasi dan Layanan Statistik',
                'nama' => $ketuaDlsNames,
                'diterima' => $forwardedAt,
                'selesai' => $publication->completed_at,
            ],
            [
                'uraian' => 'Kepala BPS Provinsi',
                'nama' => collect(['Moh Wahyu Yulianto, S.Si., S.S.T., M.Si.']),
                'diterima' => null,
                'selesai' => null,
            ],
        ];

        $pdf = Pdf::loadView('pdf.kartu-kendali', [
            'card' => $card,
            'publication' => $publication,
            'sections' => $sections,
            'answersByItem' => $answersByItem,
            'progres' => $progres,
        ])->setPaper('a4');

        $fileName = $publication->number.'-v'.$card->id.'.pdf';
        $localFallbackPath = 'kartu-kendali/'.$fileName;

        return $this->documentStorage->storePdfBytes(
            $pdf->output(),
            'kartu-kendali',
            $fileName,
            $localFallbackPath,
        );
    }
}