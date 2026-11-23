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
            ->when($roleFilter !== null, fn ($query) => $query->withAssignedRole($roleFilter))
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
        return view('users.form', [
            'user' => new User([
                'role' => Role::PetugasDls,
                'roles' => [Role::PetugasDls->value],
            ]),
            'teams' => Team::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required_without:roles', 'nullable', Rule::enum(Role::class)],
            'roles' => ['required_without:role', 'nullable', 'array', 'min:1'],
            'roles.*' => ['required', Rule::enum(Role::class)],
            'team_id' => ['nullable', 'exists:teams,id'],
        ], [], ['email' => 'akun Google (email)', 'role' => 'role', 'roles' => 'role', 'team_id' => 'tim']);

        [$activeRole, $assignedRoleValues] = $this->resolveRoles($request, null);

        $hasTeamRole = in_array(Role::TimNaskah->value, $assignedRoleValues, true)
            || in_array(Role::KetuaTim->value, $assignedRoleValues, true);

        User::create([
            'name' => $data['name'] ?? str($data['email'])->before('@')->toString(),
            'email' => mb_strtolower($data['email']),
            'role' => $activeRole,
            'roles' => $assignedRoleValues,
            'team_id' => $hasTeamRole ? ($data['team_id'] ?? null) : null,
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
            'role' => ['required_without:roles', 'nullable', Rule::enum(Role::class)],
            'roles' => ['required_without:role', 'nullable', 'array', 'min:1'],
            'roles.*' => ['required', Rule::enum(Role::class)],
            'team_id' => ['nullable', 'exists:teams,id'],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [], ['role' => 'role', 'roles' => 'role', 'team_id' => 'tim']);

        [$activeRole, $assignedRoleValues] = $this->resolveRoles($request, $user);

        if ($user->id === $request->user()->id && ! in_array(Role::KetuaDls->value, $assignedRoleValues, true)) {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah atau menghapus role Ketua DLS dari akun Anda sendiri.']);
        }

        $otherKetuaDlsCount = User::withAssignedRole(Role::KetuaDls)
            ->where('id', '!=', $user->id)
            ->count();

        if ($user->hasAssignedRole(Role::KetuaDls) && ! in_array(Role::KetuaDls->value, $assignedRoleValues, true) && $otherKetuaDlsCount < 1) {
            return back()->withErrors(['role' => 'Minimal harus ada satu akun Ketua DLS.']);
        }

        if ($user->ledTeam !== null && ! in_array(Role::KetuaTim->value, $assignedRoleValues, true)) {
            return back()->withErrors(['role' => 'Akun ini masih memimpin sebuah tim. Pindahkan kepemimpinan tim terlebih dahulu melalui menu Ketua Tim (Petugas DLS).']);
        }

        $hasTeamRole = in_array(Role::TimNaskah->value, $assignedRoleValues, true)
            || in_array(Role::KetuaTim->value, $assignedRoleValues, true);

        $teamId = null;
        if ($user->ledTeam !== null) {
            $teamId = $data['team_id'] ?? $user->ledTeam->id;
        } elseif ($hasTeamRole) {
            $teamId = array_key_exists('team_id', $data) ? ($data['team_id'] ?: null) : $user->team_id;
        }

        $user->update([
            'name' => $data['name'],
            'role' => $activeRole,
            'roles' => $assignedRoleValues,
            'team_id' => $teamId,
            'nip' => $data['nip'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * @return array{0: Role, 1: list<string>}
     */
    private function resolveRoles(Request $request, ?User $user): array
    {
        $allRoleValues = array_map(fn (Role $r) => $r->value, Role::cases());

        if ($request->filled('roles') && is_array($request->input('roles'))) {
            $raw = array_values(array_unique(array_map('strval', $request->input('roles'))));
        } else {
            $raw = [(string) $request->input('role')];
        }

        if (in_array(Role::KetuaDls->value, $raw, true)) {
            $raw = $allRoleValues;
        }

        $assignedRoleValues = array_values(array_intersect($allRoleValues, $raw));

        if ($request->filled('role') && in_array($request->string('role')->toString(), $assignedRoleValues, true)) {
            $activeRole = Role::from($request->string('role')->toString());
        } elseif ($user !== null && $user->role !== null && in_array($user->role->value, $assignedRoleValues, true)) {
            $activeRole = $user->role;
        } else {
            $priority = [Role::KetuaDls, Role::PetugasDls, Role::KetuaTim, Role::TimNaskah];
            $activeRole = Role::TimNaskah;
            foreach ($priority as $candidate) {
                if (in_array($candidate->value, $assignedRoleValues, true)) {
                    $activeRole = $candidate;
                    break;
                }
            }
        }

        return [$activeRole, $assignedRoleValues];
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
