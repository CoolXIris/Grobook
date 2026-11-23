<?php

namespace App\Services;

use App\Enums\PublicationStatus;
use App\Enums\RevisionStage;
use App\Enums\TimelineAction;
use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\QualityCard;
use App\Models\Revision;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PublicationWorkflow
{
    public function __construct(
        private KartuKendaliGenerator $kartuKendali,
        private DocumentStorage $documentStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function submit(User $user, array $attributes, string $driveLink, UploadedFile $file): Publication
    {
        return DB::transaction(function () use ($user, $attributes, $driveLink, $file) {
            $publication = Publication::create([
                ...$attributes,
                'user_id' => $user->id,
                'team_id' => $user->team_id,
                'status' => PublicationStatus::Diajukan,
                'submitted_at' => now(),
            ]);

            $publication->update([
                'number' => sprintf('PUB-%s-%05d', now()->format('Y'), $publication->id),
            ]);

            $this->storeFile($publication, $file, $driveLink, $user, 1);
            $this->record($publication, $user, TimelineAction::Submitted, 'Pengajuan publikasi '.$publication->type->label());

            return $publication;
        });
    }

    /**
     * @param  array<int, array{status: string, note: ?string}>  $answers
     */
    public function approveContent(Publication $publication, User $ketuaTim, array $answers): QualityCard
    {
        return DB::transaction(function () use ($publication, $ketuaTim, $answers) {
            $card = QualityCard::create([
                'publication_id' => $publication->id,
                'user_id' => $ketuaTim->id,
                'approved_at' => now(),
            ]);

            foreach ($answers as $itemId => $answer) {
                $card->answers()->create([
                    'checklist_item_id' => $itemId,
                    'status' => $answer['status'],
                    'note' => null,
                ]);
            }

            $card->update(['pdf_path' => $this->kartuKendali->generate($card)]);

            $publication->update(['status' => PublicationStatus::ReviewDls]);
            $this->record($publication, $ketuaTim, TimelineAction::ContentApproved, 'Kartu kendali kualitas diisi dan naskah disetujui untuk lanjut ke review Petugas DLS.');

            return $card;
        });
    }

    public function requestRevision(Publication $publication, User $requester, string $comment, ?UploadedFile $file = null): Revision
    {
        return DB::transaction(function () use ($publication, $requester, $comment, $file) {
            $stage = match ($publication->status) {
                PublicationStatus::Diajukan => RevisionStage::KetuaTim,
                PublicationStatus::ReviewDls => RevisionStage::Dls,
                PublicationStatus::ReviewAkhir => RevisionStage::KetuaDls,
                default => throw new \LogicException('Publikasi tidak dalam tahap yang dapat direvisi.'),
            };

            $filePath = null;
            $originalName = null;

            if ($file !== null) {
                $originalName = $file->getClientOriginalName();
                $driveFileName = ($publication->number ?? 'PUB-'.$publication->id).'-revisi-'.$originalName;
                $filePath = $this->documentStorage->storeUploadedPdf($file, 'revisions/'.$publication->id, $driveFileName);
            }

            $revision = Revision::create([
                'publication_id' => $publication->id,
                'requested_by' => $requester->id,
                'stage' => $stage,
                'comment' => $comment,
                'file_path' => $filePath,
                'original_name' => $originalName,
            ]);

            $publication->update(['status' => match ($stage) {
                RevisionStage::KetuaTim => PublicationStatus::RevisiKetuaTim,
                RevisionStage::Dls => PublicationStatus::RevisiDls,
                RevisionStage::KetuaDls => PublicationStatus::RevisiKetuaDls,
            }]);

            $this->record($publication, $requester, TimelineAction::RevisionRequested, $comment, [
                'stage' => $stage->value,
                'revision_id' => $revision->id,
            ]);

            return $revision;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function resubmit(Publication $publication, User $user, array $attributes, string $driveLink, ?UploadedFile $file = null): Publication
    {
        return DB::transaction(function () use ($publication, $user, $attributes, $driveLink, $file) {
            $publication->update($attributes);

            if ($file !== null) {
                $version = (int) $publication->files()->max('version') + 1;
                $this->storeFile($publication, $file, $driveLink, $user, $version);
            } else {
                $publication->latestFile->update(['drive_link' => $driveLink]);
            }

            $openRevision = $publication->openRevision;
            $openRevision?->update(['resolved_at' => now()]);

            $publication->update([
                'status' => $openRevision?->stage->returnStatus() ?? PublicationStatus::Diajukan,
            ]);

            $this->record($publication, $user, TimelineAction::Resubmitted, 'Naskah hasil revisi diajukan ulang.');

            return $publication;
        });
    }

    public function forwardToFinal(Publication $publication, User $petugas): Publication
    {
        return DB::transaction(function () use ($publication, $petugas) {
            $publication->update(['status' => PublicationStatus::ReviewAkhir]);
            $this->record($publication, $petugas, TimelineAction::ForwardedToFinal, 'Layout dan non-konten sesuai, naskah diteruskan ke Ketua DLS untuk pengecekan akhir.');

            return $publication;
        });
    }

    public function approveFinal(Publication $publication, User $ketuaDls): Publication
    {
        return DB::transaction(function () use ($publication, $ketuaDls) {
            $publication->update([
                'status' => PublicationStatus::Selesai,
                'completed_at' => now(),
            ]);

            $this->record($publication, $ketuaDls, TimelineAction::FinalApproved, 'Pengecekan akhir disetujui. Naskah siap diunggah ke portal publikasi pusat.');

            return $publication;
        });
    }

    private function storeFile(Publication $publication, UploadedFile $file, string $driveLink, User $user, int $version): PublicationFile
    {
        $originalName = $file->getClientOriginalName();
        $driveFileName = ($publication->number ?? 'PUB-'.$publication->id).'-v'.$version.'-'.$originalName;

        return PublicationFile::create([
            'publication_id' => $publication->id,
            'version' => $version,
            'file_path' => $this->documentStorage->storeUploadedPdf($file, 'publications/'.$publication->id, $driveFileName),
            'original_name' => $originalName,
            'drive_link' => $driveLink,
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function record(Publication $publication, User $user, TimelineAction $action, ?string $description = null, ?array $metadata = null): TimelineEvent
    {
        return TimelineEvent::create([
            'publication_id' => $publication->id,
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}