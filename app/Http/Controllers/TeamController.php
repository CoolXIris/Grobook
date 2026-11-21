<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\ArcPublication;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        Team::whereNull('code')->orWhere('code', '')->get()->each(function (Team $team) {
            $team->update(['code' => $this->generateDefaultCode($team->id)]);
        });

        $teams = Team::with(['ketuaTim', 'arcPublications'])
            ->withCount(['members', 'arcPublications'])
            ->orderBy('name')
            ->paginate(15);

        return view('teams.index', ['teams' => $teams]);
    }

    public function create(): View
    {
        return view('teams.form', [
            'team' => new Team(['code' => $this->generateDefaultCode()]),
            'ketuaTimEmail' => null,
            'teamMembers' => collect(),
            'arcPublications' => ArcPublication::with('team')->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:teams,code'],
            'name' => ['required', 'string', 'max:255'],
            'ketua_tim_email' => ['required', 'email', 'max:255'],
            'arc_publication_ids' => ['nullable', 'array'],
            'arc_publication_ids.*' => [
                'integer',
                Rule::exists('arc_publications', 'id')->whereNull('team_id'),
            ],
        ], [
            'arc_publication_ids.*.exists' => 'Salah satu judul publikasi ARC sudah di-assign ke tim lain.',
        ], [
            'code' => 'kode tim',
            'name' => 'nama tim',
            'ketua_tim_email' => 'akun Google ketua tim',
            'arc_publication_ids' => 'judul publikasi ARC',
        ]);

        $email = mb_strtolower($data['ketua_tim_email']);
        $ketuaTim = User::where('email', $email)->first();

        if ($ketuaTim !== null && $ketuaTim->role !== Role::KetuaTim) {
            return back()->withErrors(['ketua_tim_email' => 'Akun tersebut sudah memiliki role '.$ketuaTim->role->label().' dan tidak bisa dijadikan ketua tim.'])->withInput();
        }

        if ($ketuaTim?->ledTeam !== null) {
            return back()->withErrors(['ketua_tim_email' => 'Akun tersebut sudah memimpin tim lain.'])->withInput();
        }

        DB::transaction(function () use ($data, $email, $ketuaTim) {
            $ketuaTim ??= User::create([
                'name' => str($email)->before('@')->toString(),
                'email' => $email,
                'role' => Role::KetuaTim,
            ]);

            $code = ! empty($data['code'])
                ? mb_strtoupper(trim($data['code']))
                : $this->generateDefaultCode();

            $team = Team::create([
                'code' => $code,
                'name' => $data['name'],
                'ketua_tim_id' => $ketuaTim->id,
            ]);
            $ketuaTim->update(['team_id' => $team->id]);

            $arcIds = array_map('intval', $data['arc_publication_ids'] ?? []);
            if ($arcIds !== []) {
                ArcPublication::whereIn('id', $arcIds)->whereNull('team_id')->update(['team_id' => $team->id]);
            }
        });

        return redirect()->route('teams.index')->with('success', 'Tim dan akun ketua tim berhasil didaftarkan.');
    }

    public function edit(Team $team): View
    {
        if ($team->code === null || $team->code === '') {
            $team->update(['code' => $this->generateDefaultCode($team->id)]);
        }

        $team->load(['ketuaTim', 'arcPublications']);

        $members = User::where('team_id', $team->id)
            ->when($team->ketua_tim_id, fn ($q) => $q->orWhere('id', $team->ketua_tim_id))
            ->orderByRaw("CASE WHEN role = 'ketua_tim' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('teams.form', [
            'team' => $team,
            'ketuaTimEmail' => $team->ketuaTim?->email,
            'teamMembers' => $members,
            'arcPublications' => ArcPublication::with('team')->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('teams', 'code')->ignore($team->id)],
            'name' => ['required', 'string', 'max:255'],
            'ketua_tim_email' => ['nullable', 'email', 'max:255'],
            'arc_publication_ids' => ['nullable', 'array'],
            'arc_publication_ids.*' => [
                'integer',
                Rule::exists('arc_publications', 'id')->where(function ($query) use ($team) {
                    $query->whereNull('team_id')->orWhere('team_id', $team->id);
                }),
            ],
            'members' => ['nullable', 'array'],
            'members.*.id' => ['nullable', 'integer', 'exists:users,id'],
            'members.*.email' => ['required', 'email', 'max:255'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.role' => ['required', Rule::in([Role::KetuaTim->value, Role::TimNaskah->value])],
            'members.*.nip' => ['nullable', 'string', 'max:30'],
            'members.*.jabatan' => ['nullable', 'string', 'max:255'],
            'members.*.phone' => ['nullable', 'string', 'max:30'],
        ], [
            'arc_publication_ids.*.exists' => 'Salah satu judul publikasi ARC sudah di-assign ke tim lain.',
        ], [
            'code' => 'kode tim',
            'name' => 'nama tim',
            'ketua_tim_email' => 'akun Google ketua tim',
            'arc_publication_ids' => 'judul publikasi ARC',
            'members.*.email' => 'email anggota',
            'members.*.name' => 'nama anggota',
            'members.*.role' => 'role anggota',
        ]);

        if ($request->boolean('sync_members')) {
            $memberRows = array_values($data['members'] ?? []);
            $ketuaRows = array_filter($memberRows, fn (array $row) => $row['role'] === Role::KetuaTim->value);

            if (count($ketuaRows) !== 1) {
                return back()->withErrors(['members' => 'Harus ada tepat 1 akun dengan role Ketua Tim pada tabel anggota tim.'])->withInput();
            }

            $emails = array_map(fn (array $row) => mb_strtolower(trim($row['email'])), $memberRows);
            if (count($emails) !== count(array_unique($emails))) {
                return back()->withErrors(['members' => 'Terdapat email yang duplikat pada tabel anggota tim.'])->withInput();
            }

            foreach ($memberRows as $row) {
                $rowEmail = mb_strtolower(trim($row['email']));
                $rowId = ! empty($row['id']) ? (int) $row['id'] : null;

                $existingByEmail = User::where('email', $rowEmail)->first();
                if ($existingByEmail !== null && $rowId !== null && $existingByEmail->id !== $rowId) {
                    return back()->withErrors(['members' => "Email {$rowEmail} sudah digunakan oleh akun lain."])->withInput();
                }

                $targetUser = $rowId ? User::find($rowId) : $existingByEmail;
                if ($targetUser !== null) {
                    if (in_array($targetUser->role, [Role::PetugasDls, Role::KetuaDls], true)) {
                        return back()->withErrors(['members' => "Akun {$rowEmail} memiliki role {$targetUser->role->label()} dan tidak dapat dimasukkan ke tim."])->withInput();
                    }
                    if ($targetUser->team_id !== null && $targetUser->team_id !== $team->id) {
                        return back()->withErrors(['members' => "Akun {$rowEmail} sudah terdaftar di tim lain."])->withInput();
                    }
                    if ($targetUser->ledTeam !== null && $targetUser->ledTeam->id !== $team->id) {
                        return back()->withErrors(['members' => "Akun {$rowEmail} sudah memimpin tim lain."])->withInput();
                    }
                }
            }

            DB::transaction(function () use ($request, $team, $data, $memberRows) {
                $team->update(['ketua_tim_id' => null]);

                $keptIds = [];
                $newKetuaTimId = null;

                foreach ($memberRows as $row) {
                    $rowEmail = mb_strtolower(trim($row['email']));
                    $rowId = ! empty($row['id']) ? (int) $row['id'] : null;
                    $roleEnum = Role::from($row['role']);

                    $user = $rowId ? User::find($rowId) : User::where('email', $rowEmail)->first();

                    if ($user === null) {
                        $user = User::create([
                            'name' => $row['name'],
                            'email' => $rowEmail,
                            'role' => $roleEnum,
                            'team_id' => $team->id,
                            'nip' => $row['nip'] ?? null,
                            'jabatan' => $row['jabatan'] ?? null,
                            'phone' => $row['phone'] ?? null,
                        ]);
                    } else {
                        $user->update([
                            'name' => $row['name'],
                            'email' => $rowEmail,
                            'role' => $roleEnum,
                            'team_id' => $team->id,
                            'nip' => $row['nip'] ?? null,
                            'jabatan' => $row['jabatan'] ?? null,
                            'phone' => $row['phone'] ?? null,
                        ]);
                    }

                    $keptIds[] = $user->id;
                    if ($roleEnum === Role::KetuaTim) {
                        $newKetuaTimId = $user->id;
                    }
                }

                User::where('team_id', $team->id)
                    ->whereNotIn('id', $keptIds)
                    ->get()
                    ->each(function (User $removed) {
                        $removed->update([
                            'team_id' => null,
                            'role' => $removed->role === Role::KetuaTim ? Role::TimNaskah : $removed->role,
                        ]);
                    });

                $code = ! empty($data['code'])
                    ? mb_strtoupper(trim($data['code']))
                    : ($team->code ?: $this->generateDefaultCode($team->id));

                $team->update([
                    'code' => $code,
                    'name' => $data['name'],
                    'ketua_tim_id' => $newKetuaTimId,
                ]);

                if ($request->has('arc_publication_ids') || $request->boolean('sync_arc')) {
                    ArcPublication::where('team_id', $team->id)->update(['team_id' => null]);
                    $arcIds = array_map('intval', $data['arc_publication_ids'] ?? []);
                    if ($arcIds !== []) {
                        ArcPublication::whereIn('id', $arcIds)->whereNull('team_id')->update(['team_id' => $team->id]);
                    }
                }
            });

            return redirect()->route('teams.index')->with('success', 'Data tim dan anggota berhasil diperbarui.');
        }

        $newKetuaTim = $team->ketuaTim;
        $emailChanged = false;

        if (! empty($data['ketua_tim_email'])) {
            $email = mb_strtolower($data['ketua_tim_email']);
            if ($email !== $team->ketuaTim?->email) {
                $emailChanged = true;
                $candidate = User::where('email', $email)->first();

                if ($candidate !== null && $candidate->role !== Role::KetuaTim) {
                    return back()->withErrors(['ketua_tim_email' => 'Akun tersebut sudah memiliki role '.$candidate->role->label().' dan tidak bisa dijadikan ketua tim.'])->withInput();
                }

                if ($candidate?->ledTeam !== null && $candidate->ledTeam->id !== $team->id) {
                    return back()->withErrors(['ketua_tim_email' => 'Akun tersebut sudah memimpin tim lain.'])->withInput();
                }

                $newKetuaTim = $candidate;
            }
        }

        DB::transaction(function () use ($request, $team, $data, $emailChanged, $newKetuaTim) {
            $ketuaTimId = $team->ketua_tim_id;

            if ($emailChanged) {
                $oldKetua = $team->ketuaTim;
                $email = mb_strtolower($data['ketua_tim_email']);

                $newKetuaTim ??= User::create([
                    'name' => str($email)->before('@')->toString(),
                    'email' => $email,
                    'role' => Role::KetuaTim,
                ]);

                $ketuaTimId = $newKetuaTim->id;
                $team->update(['ketua_tim_id' => null]);

                if ($oldKetua !== null && $oldKetua->id !== $newKetuaTim->id) {
                    $oldKetua->update(['team_id' => null]);
                }

                $newKetuaTim->update(['role' => Role::KetuaTim, 'team_id' => $team->id]);
            }

            $code = ! empty($data['code'])
                ? mb_strtoupper(trim($data['code']))
                : ($team->code ?: $this->generateDefaultCode($team->id));

            $team->update([
                'code' => $code,
                'name' => $data['name'],
                'ketua_tim_id' => $ketuaTimId,
            ]);

            if ($request->has('arc_publication_ids') || $request->boolean('sync_arc')) {
                ArcPublication::where('team_id', $team->id)->update(['team_id' => null]);
                $arcIds = array_map('intval', $data['arc_publication_ids'] ?? []);
                if ($arcIds !== []) {
                    ArcPublication::whereIn('id', $arcIds)->whereNull('team_id')->update(['team_id' => $team->id]);
                }
            }
        });

        return redirect()->route('teams.index')->with('success', 'Data tim berhasil diperbarui.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        DB::transaction(function () use ($team) {
            User::where('team_id', $team->id)->update(['team_id' => null]);
            ArcPublication::where('team_id', $team->id)->update(['team_id' => null]);

            foreach ($team->publications()->with('qualityCards')->get() as $publication) {
                $publication->files()->delete();
                $publication->revisions()->delete();
                foreach ($publication->qualityCards as $card) {
                    $card->answers()->delete();
                    $card->delete();
                }
                $publication->timelineEvents()->delete();
                $publication->delete();
            }

            $team->delete();
        });

        return redirect()->route('teams.index')->with('success', 'Tim pembuat naskah berhasil dihapus.');
    }

    private function generateDefaultCode(?int $seedId = null): string
    {
        $num = $seedId ?? ((int) Team::max('id') + 1);
        if ($num < 1) {
            $num = 1;
        }

        do {
            $candidate = 'TIM-'.str_pad((string) $num, 2, '0', STR_PAD_LEFT);
            $exists = Team::where('code', $candidate)
                ->when($seedId !== null, fn ($q) => $q->where('id', '!=', $seedId))
                ->exists();
            $num++;
        } while ($exists);

        return $candidate;
    }
}
