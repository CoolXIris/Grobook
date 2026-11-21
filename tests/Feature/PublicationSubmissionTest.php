<?php

namespace Tests\Feature;

use App\Enums\Frequency;
use App\Enums\Language;
use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Enums\RevisionStage;
use App\Enums\TimelineAction;
use App\Models\ArcPublication;
use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class PublicationSubmissionTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_tim_naskah_can_submit_arc_publication(): void
    {
        Storage::fake('local');
        $this->freezeTime();
        [, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->create(['team_id' => $author->team_id]);

        $response = $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'arc',
            'arc_publication_id' => $arc->id,
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/drive/folders/abc',
        ]);

        $publication = Publication::firstOrFail();
        $response->assertRedirect(route('publications.show', $publication));

        $this->assertSame(PublicationStatus::Diajukan, $publication->status);
        $this->assertSame(PublicationType::Arc, $publication->type);
        $this->assertSame($arc->title, $publication->title);
        $this->assertSame($arc->title_en, $publication->title_en);
        $this->assertSame('2026-12-25', $publication->release_date->format('Y-m-d'));
        $this->assertSame('PUB-'.now()->format('Y').'-00001', $publication->number);
        $this->assertSame($author->id, $publication->user_id);
        $this->assertSame($author->team_id, $publication->team_id);
        $this->assertNotNull($publication->submitted_at);

        $file = $publication->files()->firstOrFail();
        $this->assertSame(1, $file->version);
        Storage::disk('local')->assertExists($file->file_path);

        $this->assertDatabaseHas('timeline_events', [
            'publication_id' => $publication->id,
            'user_id' => $author->id,
            'action' => TimelineAction::Submitted->value,
        ]);
    }

    public function test_tim_naskah_can_submit_non_arc_publication(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();

        $this->actingAs($author)->post(route('publications.store'), $this->nonArcPayload());

        $publication = Publication::firstOrFail();
        $this->assertSame(PublicationType::NonArc, $publication->type);
        $this->assertNull($publication->arc_publication_id);
        $this->assertSame('Judul Publikasi Non ARC', $publication->title);
        $this->assertSame(2026, $publication->year);
        $this->assertSame(8, $publication->pages_romawi);
        $this->assertSame(120, $publication->pages_arabic);
        $this->assertFalse($publication->has_cooperation);
        $this->assertSame('A4', $publication->paper_size);
    }

    public function test_submission_with_empty_payload_fails_required_rules(): void
    {
        [, , $author] = $this->makeTeam();

        $this->actingAs($author)->post(route('publications.store'), [])
            ->assertSessionHasErrors(['type', 'arc_publication_id', 'release_date', 'frequency', 'language', 'file', 'drive_link']);

        $this->assertDatabaseCount('publications', 0);
    }

    public function test_non_arc_submission_requires_non_arc_fields(): void
    {
        [, , $author] = $this->makeTeam();

        $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'non_arc',
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/file/d/abc',
        ])->assertSessionHasErrors([
            'title', 'year', 'pages_romawi', 'pages_arabic', 'has_cooperation',
            'published_for', 'cover_maker', 'orientation', 'paper_size',
        ]);
    }

    public function test_submission_rejects_non_pdf_file(): void
    {
        [, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->create();

        $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'arc',
            'arc_publication_id' => $arc->id,
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => UploadedFile::fake()->create('naskah.docx', 10),
            'drive_link' => 'https://drive.google.com/file/d/abc',
        ])->assertSessionHasErrors('file');
    }

    public function test_submission_rejects_inactive_arc_title(): void
    {
        [, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->inactive()->create(['team_id' => $author->team_id]);

        $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'arc',
            'arc_publication_id' => $arc->id,
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/file/d/abc',
        ])->assertSessionHasErrors('arc_publication_id');
    }

    public function test_tim_naskah_cannot_submit_arc_title_assigned_to_other_team(): void
    {
        [, , $author] = $this->makeTeam();
        [$otherTeam] = $this->makeTeam();
        $otherArc = ArcPublication::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'arc',
            'arc_publication_id' => $otherArc->id,
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/file/d/abc',
        ])->assertSessionHasErrors('arc_publication_id');
    }

    public function test_non_arc_accepts_even_roman_numeral_pages_romawi_and_rejects_odd(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();

        $oddPayload = $this->nonArcPayload();
        $oddPayload['pages_romawi'] = 'III';

        $this->actingAs($author)->post(route('publications.store'), $oddPayload)
            ->assertSessionHasErrors('pages_romawi');

        $evenPayload = $this->nonArcPayload();
        $evenPayload['pages_romawi'] = 'XIV';

        $this->actingAs($author)->post(route('publications.store'), $evenPayload)
            ->assertSessionHasNoErrors();

        $publication = Publication::firstOrFail();
        $this->assertSame(14, $publication->pages_romawi);
    }

    public function test_submission_rejects_pdf_over_30mb(): void
    {
        [, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->create(['team_id' => $author->team_id]);

        $this->actingAs($author)->post(route('publications.store'), [
            'type' => 'arc',
            'arc_publication_id' => $arc->id,
            'release_date' => '12/25/2026',
            'frequency' => Frequency::Tahunan->value,
            'language' => Language::Indonesia->value,
            'file' => UploadedFile::fake()->create('besar.pdf', 31000, 'application/pdf'),
            'drive_link' => 'https://drive.google.com/file/d/abc',
        ])->assertSessionHasErrors('file');
    }

    public function test_user_without_team_cannot_submit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('publications.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($user)->post(route('publications.store'), [])->assertForbidden();
    }

    public function test_ketua_tim_cannot_submit(): void
    {
        [, $ketuaTim] = $this->makeTeam();

        $this->actingAs($ketuaTim)->post(route('publications.store'), [])->assertForbidden();
    }

    public function test_resubmit_with_new_file_increments_version_and_returns_to_dls(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();
        $publication = Publication::factory()->nonArc()->status(PublicationStatus::RevisiDls)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id, 'version' => 1]);
        $revision = Revision::factory()->create([
            'publication_id' => $publication->id,
            'stage' => RevisionStage::Dls,
        ]);

        $payload = $this->nonArcPayload();
        $payload['title'] = 'Judul Hasil Revisi';
        unset($payload['type']);

        $this->actingAs($author)->put(route('publications.update', $publication), $payload)
            ->assertRedirect(route('publications.show', $publication));

        $publication->refresh();
        $this->assertSame(PublicationStatus::ReviewDls, $publication->status);
        $this->assertSame('Judul Hasil Revisi', $publication->title);
        $this->assertSame(2, $publication->files()->count());
        $this->assertSame(2, $publication->latestFile->version);
        $this->assertNotNull($revision->fresh()->resolved_at);
        $this->assertDatabaseHas('timeline_events', [
            'publication_id' => $publication->id,
            'action' => TimelineAction::Resubmitted->value,
        ]);
    }

    public function test_resubmit_without_file_updates_drive_link_only(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();
        $publication = Publication::factory()->nonArc()->status(PublicationStatus::RevisiKetuaTim)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        $file = PublicationFile::factory()->create(['publication_id' => $publication->id, 'version' => 1]);
        Revision::factory()->create([
            'publication_id' => $publication->id,
            'stage' => RevisionStage::KetuaTim,
        ]);

        $payload = $this->nonArcPayload();
        $payload['drive_link'] = 'https://drive.google.com/file/d/baru';
        unset($payload['type'], $payload['file']);

        $this->actingAs($author)->put(route('publications.update', $publication), $payload);

        $publication->refresh();
        $this->assertSame(PublicationStatus::Diajukan, $publication->status);
        $this->assertSame(1, $publication->files()->count());
        $this->assertSame('https://drive.google.com/file/d/baru', $file->fresh()->drive_link);
    }

    public function test_other_user_cannot_resubmit(): void
    {
        [, , $author] = $this->makeTeam();
        $other = User::factory()->inTeam()->create();
        $publication = Publication::factory()->status(PublicationStatus::RevisiDls)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($other)->put(route('publications.update', $publication), $this->nonArcPayload())
            ->assertForbidden();
    }

    public function test_cannot_edit_publication_that_is_not_in_revision(): void
    {
        [, , $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($author)->get(route('publications.edit', $publication))->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function nonArcPayload(): array
    {
        return [
            'type' => 'non_arc',
            'title' => 'Judul Publikasi Non ARC',
            'title_en' => 'Non ARC Publication Title',
            'release_date' => '06/30/2026',
            'frequency' => Frequency::Khusus->value,
            'language' => Language::Indonesia->value,
            'year' => 2026,
            'pages_romawi' => 8,
            'pages_arabic' => 120,
            'has_cooperation' => '0',
            'published_for' => 'internal',
            'cover_maker' => 'ipds',
            'orientation' => 'portrait',
            'paper_size' => 'A4',
            'file' => $this->pdfUpload(),
            'drive_link' => 'https://drive.google.com/drive/folders/xyz',
        ];
    }
}
