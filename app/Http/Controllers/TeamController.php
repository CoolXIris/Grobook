<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $teams = Team::with('ketuaTim')->withCount('members')->orderBy('name')->paginate(15);

        return view('teams.index', ['teams' => $teams]);
    }

    public function create(): View
    {
        return view('teams.form', ['team' => new Team, 'ketuaTimEmail' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ketua_tim_email' => ['required', 'email', 'max:255'],
        ], [], ['name' => 'nama tim', 'ketua_tim_email' => 'akun Google ketua tim']);

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

            $team = Team::create(['name' => $data['name'], 'ketua_tim_id' => $ketuaTim->id]);
            $ketuaTim->update(['team_id' => $team->id]);
        });

        return redirect()->route('teams.index')->with('success', 'Tim dan akun ketua tim berhasil didaftarkan.');
    }

    public function edit(Team $team): View
    {
        return view('teams.form', ['team' => $team, 'ketuaTimEmail' => $team->ketuaTim?->email]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [], ['name' => 'nama tim']);

        $team->update(['name' => $data['name']]);

        return redirect()->route('teams.index')->with('success', 'Nama tim berhasil diperbarui.');
    }
}
