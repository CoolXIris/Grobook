<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ArcPublication;
use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use App\Models\Publication;
use App\Models\QualityCardAnswer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\InteractsWithPublications;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use InteractsWithPublications;
    use LazilyRefreshDatabase;

    public function test_ketua_dls_registers_petugas_dls(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();

        $this->actingAs($ketuaDls)->post(route('users.store'), [
            'email' => 'Petugas.Baru@gmail.com',
            'role' => 'petugas_dls',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'petugas.baru@gmail.com')->firstOrFail();
        $this->assertSame(Role::PetugasDls, $user->role);
        $this->assertSame('Petugas.Baru', $user->name);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();
        User::factory()->create(['email' => 'sudah.ada@gmail.com']);

        $this->actingAs($ketuaDls)->post(route('users.store'), [
            'email' => 'sudah.ada@gmail.com',
            'role' => 'petugas_dls',
        ])->assertSessionHasErrors('email');
    }

    public function test_ketua_dls_cannot_change_own_role(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();

        $this->actingAs($ketuaDls)->put(route('users.update', $ketuaDls), [
            'name' => $ketuaDls->name,
            'role' => 'petugas_dls',
        ])->assertSessionHasErrors('role');

        $this->assertSame(Role::KetuaDls, $ketuaDls->fresh()->role);
    }

    public function test_ketua_dls_role_can_change_when_another_ketua_dls_exists(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();
        $other = User::factory()->ketuaDls()->create();

        $this->actingAs($ketuaDls)->put(route('users.update', $other), [
            'name' => $other->name,
            'role' => 'petugas_dls',
        ])->assertRedirect(route('users.index'));

        $this->assertSame(Role::PetugasDls, $other->fresh()->role);
    }

    public function test_ketua_dls_cannot_delete_self(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();

        $this->actingAs($ketuaDls)->delete(route('users.destroy', $ketuaDls))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $ketuaDls->id]);
    }

    public function test_user_with_publication_history_cannot_be_deleted(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();
        [, , $author] = $this->makeTeam();
        Publication::factory()->create(['user_id' => $author->id, 'team_id' => $author->team_id]);

        $this->actingAs($ketuaDls)->delete(route('users.destroy', $author))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $author->id]);
    }

    public function test_ketua_tim_leading_team_cannot_change_role(): void
    {
        $ketuaDls = User::factory()->ketuaDls()->create();
        [, $ketuaTim] = $this->makeTeam();

        $this->actingAs($ketuaDls)->put(route('users.update', $ketuaTim), [
            'name' => $ketuaTim->name,
            'role' => 'tim_naskah',
        ])->assertSessionHasErrors('role');

        $this->assertSame(Role::KetuaTim, $ketuaTim->fresh()->role);
    }

    public function test_ketua_tim_registers_new_member_by_email(): void
    {
        [$team, $ketuaTim] = $this->makeTeam();

        $this->actingAs($ketuaTim)->from(route('team-members.index'))->post(route('team-members.store'), [
            'email' => 'Anggota.Baru@gmail.com',
        ])->assertRedirect(route('team-members.index'));

        $member = User::where('email', 'anggota.baru@gmail.com')->firstOrFail();
        $this->assertSame(Role::TimNaskah, $member->role);
        $this->assertSame($team->id, $member->team_id);
    }

    public function test_ketua_tim_cannot_register_account_with_other_role(): void
    {
        [, $ketuaTim] = $this->makeTeam();
        $petugas = User::factory()->petugasDls()->create();

        $this->actingAs($ketuaTim)->post(route('team-members.store'), [
            'email' => $petugas->email,
        ])->assertSessionHasErrors('email');

        $this->assertNull($petugas->fresh()->team_id);
    }

    public function test_ketua_tim_cannot_take_member_of_other_team(): void
    {
        [, $ketuaTim] = $this->makeTeam();
        [, , $otherAuthor] = $this->makeTeam();

        $this->actingAs($ketuaTim)->post(route('team-members.store'), [
            'email' => $otherAuthor->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_ketua_tim_removes_member_from_team(): void
    {
        [$team, $ketuaTim, $author] = $this->makeTeam();

        $this->actingAs($ketuaTim)->from(route('team-members.index'))->delete(route('team-members.destroy', $author))
            ->assertRedirect(route('team-members.index'));

        $this->assertNull($author->fresh()->team_id);
        $this->assertDatabaseHas('users', ['id' => $author->id]);
    }

    public function test_ketua_tim_cannot_remove_member_of_other_team(): void
    {
        [, $ketuaTim] = $this->makeTeam();
        [, , $otherAuthor] = $this->makeTeam();

        $this->actingAs($ketuaTim)->delete(route('team-members.destroy', $otherAuthor))->assertForbidden();
    }

    public function test_petugas_dls_registers_team_with_new_ketua_tim(): void
    {
        $petugas = User::factory()->petugasDls()->create();

        $this->actingAs($petugas)->post(route('teams.store'), [
            'name' => 'Tim Penyusun Naskah Sosial',
            'ketua_tim_email' => 'ketua.sosial@gmail.com',
        ])->assertRedirect(route('teams.index'));

        $team = Team::where('name', 'Tim Penyusun Naskah Sosial')->firstOrFail();
        $ketua = User::where('email', 'ketua.sosial@gmail.com')->firstOrFail();
        $this->assertSame(Role::KetuaTim, $ketua->role);
        $this->assertSame($ketua->id, $team->ketua_tim_id);
        $this->assertSame($team->id, $ketua->team_id);
    }

    public function test_team_registration_rejects_account_with_other_role(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        $author = User::factory()->create();

        $this->actingAs($petugas)->post(route('teams.store'), [
            'name' => 'Tim Baru',
            'ketua_tim_email' => $author->email,
        ])->assertSessionHasErrors('ketua_tim_email');

        $this->assertDatabaseCount('teams', 0);
    }

    public function test_team_registration_rejects_ketua_of_another_team(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        [, $ketuaTim] = $this->makeTeam();

        $this->actingAs($petugas)->post(route('teams.store'), [
            'name' => 'Tim Baru',
            'ketua_tim_email' => $ketuaTim->email,
        ])->assertSessionHasErrors('ketua_tim_email');
    }

    public function test_petugas_dls_manages_checklist_items(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        $section = ChecklistSection::factory()->create();

        $this->actingAs($petugas)->post(route('checklist-items.store'), [
            'checklist_section_id' => $section->id,
            'number' => 1,
            'group_label' => 'KOVER DEPAN',
            'content' => 'Judul pada kover sesuai dengan judul publikasi.',
            'position' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('checklist-items.index'));

        $item = ChecklistItem::firstOrFail();
        $this->assertSame('KOVER DEPAN', $item->group_label);
        $this->assertTrue($item->is_active);

        $this->actingAs($petugas)->put(route('checklist-items.update', $item), [
            'checklist_section_id' => $section->id,
            'number' => 1,
            'content' => 'Judul pada kover sesuai dan terbaca jelas.',
            'position' => 1,
        ])->assertRedirect(route('checklist-items.index'));

        $item->refresh();
        $this->assertSame('Judul pada kover sesuai dan terbaca jelas.', $item->content);
        $this->assertFalse($item->is_active);

        $this->actingAs($petugas)->delete(route('checklist-items.destroy', $item));
        $this->assertDatabaseCount('checklist_items', 0);
    }

    public function test_checklist_item_used_on_quality_card_can_be_force_deleted(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        $item = ChecklistItem::factory()->create();
        $answer = QualityCardAnswer::factory()->create(['checklist_item_id' => $item->id]);

        $this->actingAs($petugas)->delete(route('checklist-items.destroy', $item))
            ->assertRedirect(route('checklist-items.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('checklist_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('quality_card_answers', ['id' => $answer->id]);
    }

    public function test_petugas_dls_manages_checklist_sections(): void
    {
        $petugas = User::factory()->petugasDls()->create();

        $this->actingAs($petugas)->post(route('checklist-sections.store'), [
            'position' => 8,
            'name' => 'PEMANFAATAN ADOBE',
        ])->assertRedirect(route('checklist-items.index'));

        $section = ChecklistSection::where('name', 'PEMANFAATAN ADOBE')->firstOrFail();
        $this->assertSame(8, $section->position);

        $this->actingAs($petugas)->put(route('checklist-sections.update', $section), [
            'position' => 9,
            'name' => 'PEMANFAATAN ADOBE INDESIGN',
        ])->assertRedirect(route('checklist-items.index'));

        $section->refresh();
        $this->assertSame(9, $section->position);
        $this->assertSame('PEMANFAATAN ADOBE INDESIGN', $section->name);

        $item = ChecklistItem::factory()->create(['checklist_section_id' => $section->id]);
        $answer = QualityCardAnswer::factory()->create(['checklist_item_id' => $item->id]);

        $this->actingAs($petugas)->delete(route('checklist-sections.destroy', $section))
            ->assertRedirect(route('checklist-items.index'));

        $this->assertDatabaseMissing('checklist_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('checklist_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('quality_card_answers', ['id' => $answer->id]);
    }

    public function test_petugas_dls_can_update_team_change_ketua_tim_assign_exclusive_arc_titles_and_delete_team(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        [$teamA, $ketuaA, $memberA] = $this->makeTeam();
        [$teamB, $ketuaB] = $this->makeTeam();
        $arc1 = ArcPublication::factory()->create();
        $arc2 = ArcPublication::factory()->create();

        $this->actingAs($petugas)->put(route('teams.update', $teamA), [
            'code' => 'EKO',
            'name' => 'Tim Ekonomi & Pertanian',
            'ketua_tim_email' => 'ketua.baru@gmail.com',
            'arc_publication_ids' => [$arc1->id],
            'sync_arc' => '1',
        ])->assertRedirect(route('teams.index'));

        $teamA->refresh();
        $this->assertSame('EKO', $teamA->code);
        $this->assertSame('Tim Ekonomi & Pertanian', $teamA->name);
        $this->assertSame('ketua.baru@gmail.com', $teamA->ketuaTim->email);
        $this->assertNull($ketuaA->fresh()->team_id);
        $this->assertSame($teamA->id, $arc1->fresh()->team_id);

        // Tim B tidak bisa mengambil judul ARC yang sudah di-assign ke Tim A
        $this->actingAs($petugas)->put(route('teams.update', $teamB), [
            'code' => 'SOS',
            'name' => $teamB->name,
            'ketua_tim_email' => $ketuaB->email,
            'arc_publication_ids' => [$arc1->id, $arc2->id],
            'sync_arc' => '1',
        ])->assertSessionHasErrors('arc_publication_ids.0');

        $this->assertSame($teamA->id, $arc1->fresh()->team_id);

        // Hapus Tim A
        $this->actingAs($petugas)->delete(route('teams.destroy', $teamA))
            ->assertRedirect(route('teams.index'));

        $this->assertDatabaseMissing('teams', ['id' => $teamA->id]);
        $this->assertNull($memberA->fresh()->team_id);
        $this->assertNull($arc1->fresh()->team_id);
    }

    public function test_petugas_dls_can_edit_team_members_table_and_auto_fills_team_code(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        [$team, $ketua, $member] = $this->makeTeam();
        $team->update(['code' => null]);

        // Membuka halaman edit tim otomatis mengisi kode tim jika sebelumnya kosong
        $this->actingAs($petugas)->get(route('teams.edit', $team))->assertOk();
        $this->assertNotNull($team->fresh()->code);

        // Edit tabel anggota tim: ubah role member jadi ketua_tim, ketua lama jadi tim_naskah, tambah anggota baru
        $this->actingAs($petugas)->put(route('teams.update', $team), [
            'code' => 'TIM-SOS-01',
            'name' => 'Tim Statistik Sosial',
            'sync_members' => '1',
            'sync_arc' => '1',
            'members' => [
                [
                    'id' => $ketua->id,
                    'email' => $ketua->email,
                    'name' => 'Eks Ketua Tim',
                    'role' => 'tim_naskah',
                    'nip' => '19800101',
                    'jabatan' => 'Statistisi Ahli Muda',
                    'phone' => '0811111111',
                ],
                [
                    'id' => $member->id,
                    'email' => 'ketua.promosi@gmail.com',
                    'name' => 'Ketua Baru Promosi',
                    'role' => 'ketua_tim',
                    'nip' => '19900202',
                    'jabatan' => 'Statistisi Ahli Madya',
                    'phone' => '0822222222',
                ],
                [
                    'id' => '',
                    'email' => 'anggota.tambahan@gmail.com',
                    'name' => 'Anggota Tambahan',
                    'role' => 'tim_naskah',
                    'nip' => '19950303',
                    'jabatan' => 'Statistisi Pertama',
                    'phone' => '0833333333',
                ],
            ],
        ])->assertRedirect(route('teams.index'));

        $team->refresh();
        $this->assertSame('TIM-SOS-01', $team->code);
        $this->assertSame($member->id, $team->ketua_tim_id);

        $ketua->refresh();
        $this->assertSame(Role::TimNaskah, $ketua->role);
        $this->assertSame('19800101', $ketua->nip);

        $member->refresh();
        $this->assertSame(Role::KetuaTim, $member->role);
        $this->assertSame('ketua.promosi@gmail.com', $member->email);
        $this->assertSame('Ketua Baru Promosi', $member->name);
        $this->assertSame('Statistisi Ahli Madya', $member->jabatan);

        $added = User::where('email', 'anggota.tambahan@gmail.com')->firstOrFail();
        $this->assertSame($team->id, $added->team_id);
        $this->assertSame(Role::TimNaskah, $added->role);
    }

    public function test_ketua_dls_defaults_to_single_role_and_can_assign_multirole_and_switch_active_role(): void
    {
        [$team] = $this->makeTeam();
        $ketuaDls = User::factory()->ketuaDls()->create();

        // Default Ketua DLS hanya memiliki role Ketua DLS saja
        $this->assertSame([Role::KetuaDls], $ketuaDls->assignedRoles());

        // Ketua DLS tidak bisa switch ke role lain jika belum di-assign
        $this->actingAs($ketuaDls)->post(route('role.switch'), [
            'role' => Role::PetugasDls->value,
        ])->assertForbidden();

        // Ketua DLS dapat mengatur multirole untuk akun lain lewat Kelola User
        $user = User::factory()->create(['role' => Role::TimNaskah]);
        $this->actingAs($ketuaDls)->put(route('users.update', $user), [
            'name' => 'Akun Multirole',
            'roles' => [Role::TimNaskah->value, Role::PetugasDls->value],
            'team_id' => $team->id,
        ])->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertTrue($user->hasAssignedRole(Role::TimNaskah));
        $this->assertTrue($user->hasAssignedRole(Role::PetugasDls));
        $this->assertFalse($user->hasAssignedRole(Role::KetuaDls));

        // User multirole bisa mengganti role aktif dari dropdown pojok kanan atas
        $this->actingAs($user)->post(route('role.switch'), [
            'role' => Role::PetugasDls->value,
        ])->assertRedirect(route('dashboard'));
        $this->assertSame(Role::PetugasDls, $user->fresh()->role);

        $this->actingAs($user)->post(route('role.switch'), [
            'role' => Role::TimNaskah->value,
        ])->assertRedirect(route('dashboard'));
        $this->assertSame(Role::TimNaskah, $user->fresh()->role);

        // User tidak bisa switch ke role yang tidak diberikan kepadanya
        $this->actingAs($user)->post(route('role.switch'), [
            'role' => Role::KetuaDls->value,
        ])->assertForbidden();

        // Jika Ketua DLS diberi multirole lewat Kelola User, ia dapat switch antar role tersebut
        $this->actingAs($ketuaDls)->put(route('users.update', $ketuaDls), [
            'name' => $ketuaDls->name,
            'roles' => [Role::KetuaDls->value, Role::PetugasDls->value],
        ])->assertRedirect(route('users.index'));

        $this->actingAs($ketuaDls->fresh())->post(route('role.switch'), [
            'role' => Role::PetugasDls->value,
        ])->assertRedirect(route('dashboard'));
        $this->assertSame(Role::PetugasDls, $ketuaDls->fresh()->role);

        $this->actingAs($ketuaDls->fresh())->post(route('role.switch'), [
            'role' => Role::KetuaDls->value,
        ])->assertRedirect(route('dashboard'));
        $this->assertSame(Role::KetuaDls, $ketuaDls->fresh()->role);
    }

    public function test_petugas_dls_manages_arc_titles(): void
    {
        $petugas = User::factory()->petugasDls()->create();

        $this->actingAs($petugas)->post(route('arc-publications.store'), [
            'title' => 'Provinsi Sumatera Selatan Dalam Angka 2026',
            'title_en' => 'Sumatera Selatan Province in Figures 2026',
            'is_active' => '1',
        ])->assertRedirect(route('arc-publications.index'));

        $arc = ArcPublication::firstOrFail();

        $this->actingAs($petugas)->put(route('arc-publications.update', $arc), [
            'title' => 'Provinsi Sumatera Selatan Dalam Angka 2027',
            'title_en' => 'Sumatera Selatan Province in Figures 2027',
        ]);
        $arc->refresh();
        $this->assertSame('Provinsi Sumatera Selatan Dalam Angka 2027', $arc->title);
        $this->assertFalse($arc->is_active);

        $this->actingAs($petugas)->delete(route('arc-publications.destroy', $arc));
        $this->assertDatabaseCount('arc_publications', 0);
    }

    public function test_arc_title_used_by_publication_cannot_be_deleted(): void
    {
        $petugas = User::factory()->petugasDls()->create();
        [, , $author] = $this->makeTeam();
        $arc = ArcPublication::factory()->create();
        Publication::factory()->create([
            'user_id' => $author->id,
            'team_id' => $author->team_id,
            'arc_publication_id' => $arc->id,
        ]);

        $this->actingAs($petugas)->delete(route('arc-publications.destroy', $arc))
            ->assertRedirect(route('arc-publications.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('arc_publications', ['id' => $arc->id]);
    }
}
