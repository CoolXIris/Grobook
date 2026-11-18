<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = Role::tryFrom($request->string('role')->toString());

        $users = User::with('team')
            ->when($roleFilter !== null, fn ($query) => $query->where('role', $roleFilter))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::cases(),
            'roleFilter' => $roleFilter,
        ]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User(['role' => Role::PetugasDls]), 'teams' => Team::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
        ], [], ['email' => 'akun Google (email)', 'role' => 'role']);

        User::create([
            'name' => $data['name'] ?? str($data['email'])->before('@')->toString(),
            'email' => mb_strtolower($data['email']),
            'role' => $data['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'Akun berhasil didaftarkan. Akun akan aktif saat yang bersangkutan login dengan Google.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user, 'teams' => Team::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(Role::class)],
            'team_id' => ['nullable', 'exists:teams,id'],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [], ['role' => 'role', 'team_id' => 'tim']);

        $newRole = Role::from($data['role']);

        if ($user->id === $request->user()->id && $newRole !== $request->user()->role) {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        if ($user->role === Role::KetuaDls && $newRole !== Role::KetuaDls && User::where('role', Role::KetuaDls)->count() <= 1) {
            return back()->withErrors(['role' => 'Minimal harus ada satu akun Ketua DLS.']);
        }

        if ($user->role === Role::KetuaTim && $newRole !== Role::KetuaTim && $user->ledTeam !== null) {
            return back()->withErrors(['role' => 'Akun ini masih memimpin sebuah tim. Pindahkan kepemimpinan tim terlebih dahulu melalui menu Ketua Tim (Petugas DLS).']);
        }

        $user->update([
            'name' => $data['name'],
            'role' => $newRole,
            'team_id' => $newRole === Role::TimNaskah ? ($data['team_id'] ?? null) : null,
            'nip' => $data['nip'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->publications()->exists() || $user->qualityCards()->exists()) {
            return back()->withErrors(['user' => 'Akun memiliki riwayat publikasi sehingga tidak dapat dihapus. Ubah role-nya saja bila perlu.']);
        }

        if ($user->ledTeam !== null) {
            return back()->withErrors(['user' => 'Akun ini masih memimpin sebuah tim sehingga tidak dapat dihapus.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun berhasil dihapus.');
    }
}
