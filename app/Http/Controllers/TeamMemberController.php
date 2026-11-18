<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $team = $request->user()->ledTeam;

        if ($team === null) {
            return redirect()->route('dashboard')->with('error', 'Anda belum memiliki tim. Hubungi Petugas DLS untuk mendaftarkan tim Anda.');
        }

        $team->load(['members' => fn ($query) => $query->orderBy('name')]);

        return view('team-members.index', ['team' => $team]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = $request->user()->ledTeam;
        abort_if($team === null, 403, 'Anda belum memiliki tim.');

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [], ['email' => 'akun Google (email)']);

        $email = mb_strtolower($data['email']);
        $member = User::where('email', $email)->first();

        if ($member === null) {
            User::create([
                'name' => str($email)->before('@')->toString(),
                'email' => $email,
                'role' => Role::TimNaskah,
                'team_id' => $team->id,
            ]);

            return back()->with('success', 'Akun tim pembuat naskah berhasil didaftarkan. Akun akan aktif saat yang bersangkutan login dengan Google.');
        }

        if ($member->role !== Role::TimNaskah) {
            return back()->withErrors(['email' => 'Akun tersebut sudah memiliki role '.$member->role->label().' dan tidak bisa dijadikan anggota tim.']);
        }

        if ($member->team_id !== null && $member->team_id !== $team->id) {
            return back()->withErrors(['email' => 'Akun tersebut sudah terdaftar di tim lain.']);
        }

        $member->update(['team_id' => $team->id]);

        return back()->with('success', 'Akun berhasil dimasukkan ke tim Anda.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $team = $request->user()->ledTeam;
        abort_if($team === null || $user->team_id !== $team->id || $user->role !== Role::TimNaskah, 403);

        $user->update(['team_id' => null]);

        return back()->with('success', 'Anggota berhasil dikeluarkan dari tim. Akun tetap bisa login, namun tidak dapat mengajukan publikasi sampai didaftarkan kembali.');
    }
}
