<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\ArcPublication;
use App\Models\ChecklistItem;
use App\Models\Publication;
use App\Models\PublicationFile;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_tim_naskah_pages_render(): void
    {
        Storage::fake('local');
        [$team, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->create();
        $publication = Publication::factory()->status(PublicationStatus::RevisiDls)->create([
            'user_id' => $author->id,
            'team_id' => $team->id,
            'number' => 'PUB-2026-00001',
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id]);
        Revision::factory()->create(['publication_id' => $publication->id]);

        $this->actingAs($author)->get(route('dashboard'))->assertOk();
        $this->actingAs($author)->get(route('publications.index'))->assertOk();
        $this->actingAs($author)->get(route('publications.create'))->assertOk();
        $this->actingAs($author)->get(route('publications.create-form', ['type' => 'arc']))->assertOk();
        $this->actingAs($author)->get(route('publications.create-form', ['type' => 'non_arc']))->assertOk();
        $this->actingAs($author)->get(route('publications.show', $publication))->assertOk();
        $this->actingAs($author)->get(route('publications.edit', $publication))->assertOk();
        $this->actingAs($author)->get(route('history.index'))->assertOk();
    }

    public function test_ketua_tim_pages_render(): void
    {
        Storage::fake('local');
        [$team, $ketuaTim, $author] = $this->makeTeam();
        ChecklistItem::factory()->count(3)->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $team->id,
            'number' => 'PUB-2026-00001',
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id]);

        $this->actingAs($ketuaTim)->get(route('dashboard'))->assertOk();
        $this->actingAs($ketuaTim)->get(route('reviews.content.index'))->assertOk();
        $this->actingAs($ketuaTim)->get(route('quality-cards.create', $publication))->assertOk();
        $this->actingAs($ketuaTim)->get(route('team-members.index'))->assertOk();
        $this->actingAs($ketuaTim)->get(route('publications.show', $publication))->assertOk();
        $this->actingAs($ketuaTim)->get(route('history.index'))->assertOk();
    }

    public function test_petugas_dls_pages_render(): void
    {
        Storage::fake('local');
        [$team, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $arc = ArcPublication::factory()->create();
        $item = ChecklistItem::factory()->create();
        $publication = Publication::factory()->status(PublicationStatus::Selesai)->create([
            'user_id' => $author->id,
            'team_id' => $team->id,
            'number' => 'PUB-2026-00001',
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id]);

        $this->actingAs($petugas)->get(route('dashboard'))->assertOk();
        $this->actingAs($petugas)->get(route('reviews.dls.index'))->assertOk();
        $this->actingAs($petugas)->get(route('checklist-items.index'))->assertOk();
        $this->actingAs($petugas)->get(route('checklist-items.create'))->assertOk();
        $this->actingAs($petugas)->get(route('checklist-items.edit', $item))->assertOk();
        $this->actingAs($petugas)->get(route('arc-publications.index'))->assertOk();
        $this->actingAs($petugas)->get(route('arc-publications.create'))->assertOk();
        $this->actingAs($petugas)->get(route('arc-publications.edit', $arc))->assertOk();
        $this->actingAs($petugas)->get(route('teams.index'))->assertOk();
        $this->actingAs($petugas)->get(route('teams.create'))->assertOk();
        $this->actingAs($petugas)->get(route('teams.edit', $team))->assertOk();
        $this->actingAs($petugas)->get(route('documents.index'))->assertOk();
        $this->actingAs($petugas)->get(route('publications.show', $publication))->assertOk();
        $this->actingAs($petugas)->get(route('history.index'))->assertOk();
    }

    public function test_ketua_dls_pages_render(): void
    {
        Storage::fake('local');
        [$team, , $author] = $this->makeTeam();
        $ketuaDls = User::factory()->ketuaDls()->create();
        $publication = Publication::factory()->status(PublicationStatus::ReviewAkhir)->create([
            'user_id' => $author->id,
            'team_id' => $team->id,
            'number' => 'PUB-2026-00001',
        ]);
        PublicationFile::factory()->create(['publication_id' => $publication->id]);

        $this->actingAs($ketuaDls)->get(route('dashboard'))->assertOk();
        $this->actingAs($ketuaDls)->get(route('reviews.final.index'))->assertOk();
        $this->actingAs($ketuaDls)->get(route('users.index'))->assertOk();
        $this->actingAs($ketuaDls)->get(route('users.create'))->assertOk();
        $this->actingAs($ketuaDls)->get(route('users.edit', $author))->assertOk();
        $this->actingAs($ketuaDls)->get(route('publications.show', $publication))->assertOk();
        $this->actingAs($ketuaDls)->get(route('history.index'))->assertOk();
    }
}
