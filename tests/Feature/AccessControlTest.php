<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_view_policy_matrix(): void
    {
        [$team, $ketuaTim, $author] = $this->makeTeam();
        [, $otherKetua, $otherAuthor] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $team->id,
        ]);

        $this->assertTrue(Gate::forUser($author)->allows('view', $publication));
        $this->assertTrue(Gate::forUser($ketuaTim)->allows('view', $publication));
        $this->assertTrue(Gate::forUser($petugas)->allows('view', $publication));
        $this->assertTrue(Gate::forUser($ketuaDls)->allows('view', $publication));
        $this->assertFalse(Gate::forUser($otherAuthor)->allows('view', $publication));
        $this->assertFalse(Gate::forUser($otherKetua)->allows('view', $publication));
    }

    public function test_review_policy_matrix(): void
    {
        [$team, $ketuaTim, $author] = $this->makeTeam();
        [, $otherKetua] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();

        $diajukan = Publication::factory()->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $reviewDls = Publication::factory()->status(PublicationStatus::ReviewDls)->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $reviewAkhir = Publication::factory()->status(PublicationStatus::ReviewAkhir)->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $selesai = Publication::factory()->status(PublicationStatus::Selesai)->create(['user_id' => $author->id, 'team_id' => $team->id]);

        $this->assertTrue(Gate::forUser($ketuaTim)->allows('reviewContent', $diajukan));
        $this->assertFalse(Gate::forUser($otherKetua)->allows('reviewContent', $diajukan));
        $this->assertFalse(Gate::forUser($ketuaTim)->allows('reviewContent', $reviewDls));
        $this->assertFalse(Gate::forUser($petugas)->allows('reviewContent', $diajukan));

        $this->assertTrue(Gate::forUser($petugas)->allows('reviewDls', $reviewDls));
        $this->assertFalse(Gate::forUser($petugas)->allows('reviewDls', $diajukan));
        $this->assertFalse(Gate::forUser($ketuaDls)->allows('reviewDls', $reviewDls));

        $this->assertTrue(Gate::forUser($ketuaDls)->allows('reviewFinal', $reviewAkhir));
        $this->assertFalse(Gate::forUser($ketuaDls)->allows('reviewFinal', $reviewDls));
        $this->assertFalse(Gate::forUser($petugas)->allows('reviewFinal', $reviewAkhir));

        $this->assertTrue(Gate::forUser($petugas)->allows('downloadDocuments', $selesai));
        $this->assertTrue(Gate::forUser($ketuaDls)->allows('downloadDocuments', $selesai));
        $this->assertFalse(Gate::forUser($author)->allows('downloadDocuments', $selesai));
        $this->assertFalse(Gate::forUser($petugas)->allows('downloadDocuments', $reviewAkhir));
    }

    public function test_request_revision_policy_follows_stage(): void
    {
        [$team, $ketuaTim, $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();

        $diajukan = Publication::factory()->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $reviewDls = Publication::factory()->status(PublicationStatus::ReviewDls)->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $reviewAkhir = Publication::factory()->status(PublicationStatus::ReviewAkhir)->create(['user_id' => $author->id, 'team_id' => $team->id]);
        $revisi = Publication::factory()->status(PublicationStatus::RevisiDls)->create(['user_id' => $author->id, 'team_id' => $team->id]);

        $this->assertTrue(Gate::forUser($ketuaTim)->allows('requestRevision', $diajukan));
        $this->assertFalse(Gate::forUser($petugas)->allows('requestRevision', $diajukan));
        $this->assertTrue(Gate::forUser($petugas)->allows('requestRevision', $reviewDls));
        $this->assertFalse(Gate::forUser($ketuaTim)->allows('requestRevision', $reviewDls));
        $this->assertTrue(Gate::forUser($ketuaDls)->allows('requestRevision', $reviewAkhir));
        $this->assertFalse(Gate::forUser($petugas)->allows('requestRevision', $reviewAkhir));
        $this->assertFalse(Gate::forUser($petugas)->allows('requestRevision', $revisi));
        $this->assertFalse(Gate::forUser($author)->allows('requestRevision', $diajukan));
    }

    public function test_tim_naskah_cannot_view_other_users_publication(): void
    {
        [, , $author] = $this->makeTeam();
        $other = User::factory()->inTeam()->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($other)->get(route('publications.show', $publication))->assertForbidden();
    }

    public function test_ketua_tim_cannot_view_other_teams_publication(): void
    {
        [, , $author] = $this->makeTeam();
        [, $otherKetua] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($otherKetua)->get(route('publications.show', $publication))->assertForbidden();
    }

    public function test_role_middleware_blocks_wrong_roles(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $ketuaDls = User::factory()->ketuaDls()->create();

        $this->actingAs($author)->get(route('checklist-items.index'))->assertForbidden();
        $this->actingAs($author)->get(route('users.index'))->assertForbidden();
        $this->actingAs($ketuaTim)->get(route('reviews.dls.index'))->assertForbidden();
        $this->actingAs($ketuaTim)->get(route('users.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('users.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('reviews.final.index'))->assertForbidden();
        $this->actingAs($ketuaDls)->get(route('checklist-items.index'))->assertForbidden();
        $this->actingAs($ketuaDls)->get(route('team-members.index'))->assertForbidden();
    }

    public function test_publication_list_is_scoped_per_role(): void
    {
        [$team, $ketuaTim, $author] = $this->makeTeam();
        [, , $otherAuthor] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();

        Publication::factory()->create(['user_id' => $author->id, 'team_id' => $team->id]);
        Publication::factory()->create(['user_id' => $otherAuthor->id, 'team_id' => $otherAuthor->team_id]);

        $this->actingAs($author)->get(route('publications.index'))
            ->assertViewHas('publications', fn ($publications) => $publications->total() === 1);
        $this->actingAs($ketuaTim)->get(route('publications.index'))
            ->assertViewHas('publications', fn ($publications) => $publications->total() === 1);
        $this->actingAs($petugas)->get(route('publications.index'))
            ->assertViewHas('publications', fn ($publications) => $publications->total() === 2);
    }

    public function test_history_only_shows_completed_publications_visible_to_role(): void
    {
        [$team, , $author] = $this->makeTeam();
        [, , $otherAuthor] = $this->makeTeam();

        Publication::factory()->status(PublicationStatus::Selesai)->create(['user_id' => $author->id, 'team_id' => $team->id]);
        Publication::factory()->create(['user_id' => $author->id, 'team_id' => $team->id]);
        Publication::factory()->status(PublicationStatus::Selesai)->create(['user_id' => $otherAuthor->id, 'team_id' => $otherAuthor->team_id]);

        $this->actingAs($author)->get(route('history.index'))
            ->assertViewHas('publications', fn ($publications) => $publications->total() === 1);
    }

    public function test_file_download_respects_publication_visibility(): void
    {
        Storage::fake('local');
        [, , $author] = $this->makeTeam();
        $other = User::factory()->inTeam()->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        $file = PublicationFile::factory()->create(['publication_id' => $publication->id]);
        Storage::disk('local')->put($file->file_path, '%PDF-1.4 isi');

        $this->actingAs($author)->get(route('files.publication', $file))->assertOk();
        $this->actingAs($other)->get(route('files.publication', $file))->assertForbidden();
    }
}
