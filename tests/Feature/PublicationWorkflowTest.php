<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\RevisionStage;
use App\Enums\TimelineAction;
use App\Models\ArcPublication;
use App\Models\ChecklistItem;
use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class PublicationWorkflowTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_full_workflow_from_submission_to_download(): void
    {
        Storage::fake('local');
        [, $ketuaTim, $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();
        $arc = ArcPublication::factory()->create(['team_id' => $author->team_id]);
        $items = ChecklistItem::factory()->count(2)->create();

        $this->actingAs($author)->post(route('publications.store'), $this->arcPayload($arc));
        $publication = Publication::firstOrFail();
        $this->assertSame(PublicationStatus::Diajukan, $publication->status);

        $answers = $items->mapWithKeys(fn ($item) => [$item->id => ['status' => 'sesuai']])->all();
        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), ['answers' => $answers]);
        $this->assertSame(PublicationStatus::ReviewDls, $publication->fresh()->status);

        $this->actingAs($petugas)->post(route('revisions.store', $publication), [
            'comment' => 'Perbaiki tata letak halaman katalog.',
            'file' => $this->pdfUpload('detail-revisi.pdf'),
        ]);
        $this->assertSame(PublicationStatus::RevisiDls, $publication->fresh()->status);

        $this->actingAs($author)->put(route('publications.update', $publication), [
            ...$this->arcPayload($arc),
            'file' => $this->pdfUpload('naskah-revisi.pdf'),
        ]);
        $publication->refresh();
        $this->assertSame(PublicationStatus::ReviewDls, $publication->status);
        $this->assertSame(2, $publication->files()->count());

        $this->actingAs($petugas)->post(route('reviews.dls.forward', $publication));
        $this->assertSame(PublicationStatus::ReviewAkhir, $publication->fresh()->status);

        $this->actingAs($ketuaDls)->post(route('reviews.final.approve', $publication));
        $publication->refresh();
        $this->assertSame(PublicationStatus::Selesai, $publication->status);
        $this->assertNotNull($publication->completed_at);

        $this->assertSame([
            TimelineAction::Submitted->value,
            TimelineAction::ContentApproved->value,
            TimelineAction::RevisionRequested->value,
            TimelineAction::Resubmitted->value,
            TimelineAction::ForwardedToFinal->value,
            TimelineAction::FinalApproved->value,
        ], $publication->timelineEvents()->pluck('action')->map(fn ($action) => $action->value)->all());

        $response = $this->actingAs($petugas)->get(route('documents.download', $publication));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');

        $this->actingAs($author)->get(route('documents.download', $publication))->assertForbidden();
    }

    public function test_dls_revision_requires_detail_pdf(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $publication = Publication::factory()->status(PublicationStatus::ReviewDls)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($petugas)->post(route('revisions.store', $publication), [
            'comment' => 'Perbaiki layout.',
        ])->assertSessionHasErrors('file');

        $this->assertSame(PublicationStatus::ReviewDls, $publication->fresh()->status);
    }

    public function test_ketua_tim_revision_does_not_require_pdf(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($ketuaTim)->post(route('revisions.store', $publication), [
            'comment' => 'Perbaiki isi bab pendahuluan.',
        ])->assertRedirect(route('publications.show', $publication));

        $publication->refresh();
        $this->assertSame(PublicationStatus::RevisiKetuaTim, $publication->status);
        $revision = $publication->revisions()->firstOrFail();
        $this->assertSame(RevisionStage::KetuaTim, $revision->stage);
        $this->assertNull($revision->file_path);
    }

    public function test_ketua_dls_revision_returns_to_dls_review_after_resubmit(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();
        $arc = ArcPublication::factory()->create(['team_id' => $author->team_id]);
        $publication = Publication::factory()->status(PublicationStatus::ReviewAkhir)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
            'arc_publication_id' => $arc->id,
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id, 'version' => 1]);

        $this->actingAs($ketuaDls)->post(route('revisions.store', $publication), [
            'comment' => 'Perbaiki konsistensi istilah.',
        ]);
        $this->assertSame(PublicationStatus::RevisiKetuaDls, $publication->fresh()->status);

        $this->actingAs($author)->put(route('publications.update', $publication), $this->arcPayload($arc));
        $this->assertSame(PublicationStatus::ReviewDls, $publication->fresh()->status);

        $this->actingAs($petugas)->post(route('reviews.dls.forward', $publication));
        $this->assertSame(PublicationStatus::ReviewAkhir, $publication->fresh()->status);
    }

    public function test_tim_naskah_cannot_request_revision(): void
    {
        [, , $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($author)->post(route('revisions.store', $publication), [
            'comment' => 'Coba revisi sendiri.',
        ])->assertForbidden();
    }

    public function test_revision_rejected_when_publication_already_in_revision(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->status(PublicationStatus::RevisiKetuaTim)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($ketuaTim)->post(route('revisions.store', $publication), [
            'comment' => 'Revisi lagi.',
        ])->assertForbidden();
    }

    public function test_forward_requires_review_dls_status(): void
    {
        [, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($petugas)->post(route('reviews.dls.forward', $publication))->assertForbidden();
    }

    public function test_final_approval_requires_review_akhir_status(): void
    {
        [, , $author] = $this->makeTeam();
        $ketuaDls = User::factory()->ketuaDls()->create();
        $publication = Publication::factory()->status(PublicationStatus::ReviewDls)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($ketuaDls)->post(route('reviews.final.approve', $publication))->assertForbidden();
    }

    public function test_download_rejected_before_publication_is_complete(): void
    {
        [, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $publication = Publication::factory()->status(PublicationStatus::ReviewAkhir)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($petugas)->get(route('documents.download', $publication))->assertForbidden();
    }

    public function test_pdfs_are_stored_and_served_from_google_drive_when_oauth_token_is_present(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://www.googleapis.com/drive/v3/files*' => \Illuminate\Support\Facades\Http::sequence()
                ->push(['files' => [['id' => 'folder-root', 'name' => 'Grobook BPS Sumsel']]], 200)
                ->push(['files' => [['id' => 'folder-pub', 'name' => 'publications']]], 200)
                ->push(['id' => 'folder-root', 'trashed' => false], 200)
                ->push(['files' => [['id' => 'folder-kk', 'name' => 'kartu-kendali']]], 200)
                ->push(['id' => 'folder-root', 'trashed' => false], 200)
                ->push(['files' => [['id' => 'folder-rev', 'name' => 'revisions']]], 200)
                ->push('%PDF-1.4 drive-pdf-content', 200)
                ->push('%PDF-1.4 drive-pdf-content', 200)
                ->push('%PDF-1.4 drive-kk-content', 200),
            'https://www.googleapis.com/upload/drive/v3/files*' => \Illuminate\Support\Facades\Http::sequence()
                ->push(['id' => 'gdrive-file-pub-1'], 200)
                ->push(['id' => 'gdrive-file-kk-1'], 200)
                ->push(['id' => 'gdrive-file-rev-1'], 200),
        ]);

        [, $ketuaTim, $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create([
            'google_access_token' => 'ya29.valid-admin-token',
            'google_token_expires_at' => now()->addHour(),
        ]);
        $arc = ArcPublication::factory()->create(['team_id' => $author->team_id]);
        $item = ChecklistItem::factory()->create();

        // 1. Upload PDF Naskah -> tersimpan ke Google Drive
        $this->actingAs($author)->post(route('publications.store'), $this->arcPayload($arc));
        $publication = Publication::firstOrFail();
        $pubFile = $publication->files()->firstOrFail();
        $this->assertSame('gdrive:gdrive-file-pub-1', $pubFile->file_path);

        // 2. Generate PDF Kartu Kendali -> tersimpan ke Google Drive
        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), [
            'answers' => [$item->id => ['status' => 'sesuai']],
        ]);
        $card = $publication->qualityCards()->firstOrFail();
        $this->assertSame('gdrive:gdrive-file-kk-1', $card->pdf_path);

        // 3. Upload PDF Detail Revisi -> tersimpan ke Google Drive
        $this->actingAs($petugas)->post(route('revisions.store', $publication), [
            'comment' => 'Revisi via Google Drive.',
            'file' => $this->pdfUpload('revisi.pdf'),
        ]);
        $revision = $publication->revisions()->firstOrFail();
        $this->assertSame('gdrive:gdrive-file-rev-1', $revision->file_path);

        // 4. Buka file PDF dari Google Drive lewat FileController
        $this->actingAs($author)->get(route('files.publication', $pubFile))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // 5. Unduh paket ZIP dari Google Drive saat publikasi selesai
        $publication->update(['status' => PublicationStatus::Selesai, 'completed_at' => now()]);
        $this->actingAs($petugas)->get(route('documents.download', $publication))
            ->assertOk()
            ->assertHeader('content-type', 'application/zip');
    }

    /**
     * @return array<string, mixed>
     */
    private function arcPayload(ArcPublication $arc): array
    {
        return [
            'type' => 'arc',
            'arc_publication_id' => $arc->id,
            'release_date' => '12/25/2026',
            'frequency' => 'tahunan',
            'language' => 'indonesia',
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/drive/folders/abc',
        ];
    }
}
