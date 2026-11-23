<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::enum(Role::class)],
        ]);

        $user = $request->user();
        $targetRole = Role::from($data['role']);

        if (! $user->hasAssignedRole($targetRole)) {
            abort(403, 'Anda tidak memiliki akses ke role tersebut.');
        }

        $attributes = [
            'role' => $targetRole,
            'roles' => array_map(fn (Role $r) => $r->value, $user->assignedRoles()),
        ];

        if (in_array($targetRole, [Role::TimNaskah, Role::KetuaTim], true) && $user->team_id === null) {
            $defaultTeamId = $user->ledTeam?->id ?? ($user->isSuperAdmin() ? Team::orderBy('id')->value('id') : null);
            if ($defaultTeamId !== null) {
                $attributes['team_id'] = $defaultTeamId;
            }
        }

        $user->update($attributes);

        return redirect()->route('dashboard')->with('success', 'Role aktif berhasil diubah menjadi '.$targetRole->label().'.');
    }
}
