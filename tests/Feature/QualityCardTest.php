<?php

namespace Tests\Feature;

use App\Enums\ChecklistStatus;
use App\Enums\PublicationStatus;
use App\Enums\TimelineAction;
use App\Models\ChecklistItem;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class QualityCardTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_ketua_tim_approves_with_complete_checklist_and_pdf_is_generated(): void
    {
        Storage::fake('local');
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
            'number' => 'PUB-2026-00001',
        ]);
        $items = ChecklistItem::factory()->count(3)->create();
        $inactive = ChecklistItem::factory()->create(['is_active' => false]);

        $answers = [];
        foreach ($items as $item) {
            $answers[$item->id] = ['status' => ChecklistStatus::Sesuai->value, 'note' => null];
        }
        $answers[$items[0]->id] = ['status' => ChecklistStatus::TidakRelevan->value, 'note' => 'Tidak berlaku untuk publikasi ini.'];

        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), ['answers' => $answers])
            ->assertRedirect(route('publications.show', $publication));

        $this->assertSame(PublicationStatus::ReviewDls, $publication->fresh()->status);

        $card = $publication->qualityCards()->firstOrFail();
        $this->assertSame($ketuaTim->id, $card->user_id);
        $this->assertNotNull($card->approved_at);
        $this->assertNotNull($card->pdf_path);
        Storage::disk('local')->assertExists($card->pdf_path);
        $this->assertSame(3, $card->answers()->count());
        $this->assertDatabaseHas('quality_card_answers', [
            'quality_card_id' => $card->id,
            'checklist_item_id' => $items[0]->id,
            'status' => ChecklistStatus::TidakRelevan->value,
            'note' => 'Tidak berlaku untuk publikasi ini.',
        ]);
        $this->assertDatabaseMissing('quality_card_answers', [
            'quality_card_id' => $card->id,
            'checklist_item_id' => $inactive->id,
        ]);
        $this->assertDatabaseHas('timeline_events', [
            'publication_id' => $publication->id,
            'action' => TimelineAction::ContentApproved->value,
        ]);
    }

    public function test_approval_requires_every_active_item_answered(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        $items = ChecklistItem::factory()->count(3)->create();

        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), [
            'answers' => [
                $items[0]->id => ['status' => 'sesuai'],
                $items[1]->id => ['status' => 'sesuai'],
            ],
        ])->assertSessionHasErrors('answers');

        $this->assertSame(PublicationStatus::Diajukan, $publication->fresh()->status);
        $this->assertDatabaseCount('quality_cards', 0);
    }

    public function test_tidak_sesuai_blocks_approval(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        $items = ChecklistItem::factory()->count(2)->create();

        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), [
            'answers' => [
                $items[0]->id => ['status' => 'sesuai'],
                $items[1]->id => ['status' => 'tidak_sesuai'],
            ],
        ])->assertSessionHasErrors('answers');

        $this->assertSame(PublicationStatus::Diajukan, $publication->fresh()->status);
    }

    public function test_answering_inactive_item_is_rejected(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        $active = ChecklistItem::factory()->create();
        $inactive = ChecklistItem::factory()->create(['is_active' => false]);

        $this->actingAs($ketuaTim)->post(route('quality-cards.store', $publication), [
            'answers' => [
                $active->id => ['status' => 'sesuai'],
                $inactive->id => ['status' => 'sesuai'],
            ],
        ])->assertSessionHasErrors('answers');
    }

    public function test_ketua_tim_from_other_team_cannot_approve(): void
    {
        [, , $author] = $this->makeTeam();
        [, $otherKetua] = $this->makeTeam();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);
        ChecklistItem::factory()->create();

        $this->actingAs($otherKetua)->post(route('quality-cards.store', $publication), [
            'answers' => [],
        ])->assertForbidden();
    }

    public function test_petugas_dls_cannot_approve_content(): void
    {
        [, , $author] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($petugas)->post(route('quality-cards.store', $publication), [
            'answers' => [],
        ])->assertForbidden();
    }

    public function test_kartu_kendali_form_only_shows_for_submitted_publication(): void
    {
        [, $ketuaTim, $author] = $this->makeTeam();
        $publication = Publication::factory()->status(PublicationStatus::ReviewDls)->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
        ]);

        $this->actingAs($ketuaTim)->get(route('quality-cards.create', $publication))->assertForbidden();
    }
}
