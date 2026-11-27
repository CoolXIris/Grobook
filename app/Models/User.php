<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'google_id', 'google_access_token', 'google_refresh_token',
    'google_token_expires_at', 'avatar', 'role', 'roles', 'nip', 'jabatan', 'phone', 'team_id',
])]
#[Hidden(['password', 'remember_token', 'google_access_token', 'google_refresh_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            $allRoleValues = array_map(fn (Role $r) => $r->value, Role::cases());
            $rawRoles = is_array($user->roles) ? $user->roles : [];

            $normalized = [];
            foreach ($rawRoles as $r) {
                $val = $r instanceof Role ? $r->value : (is_string($r) ? $r : null);
                if ($val !== null && in_array($val, $allRoleValues, true)) {
                    $normalized[] = $val;
                }
            }

            // If `role` was directly modified and `roles` was not explicitly changed,
            // replace single-role array only when the user previously had at most 1 role.
            if ($user->isDirty('role') && ! $user->isDirty('roles') && $user->role !== null && count($normalized) <= 1) {
                $normalized = [$user->role->value];
            }

            if ($normalized === [] && $user->role !== null) {
                $normalized = [$user->role->value];
            }

            if ($user->role !== null && ! in_array($user->role->value, $normalized, true)) {
                $normalized[] = $user->role->value;
            }

            // Preserve canonical order from Role::cases()
            $ordered = array_values(array_intersect($allRoleValues, array_unique($normalized)));

            if ($user->role === null && $ordered !== []) {
                $user->role = Role::from($ordered[0]);
            }

            $user->roles = $ordered;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'roles' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function ledTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'ketua_tim_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function qualityCards(): HasMany
    {
        return $this->hasMany(QualityCard::class);
    }

    /**
     * All Role enums assigned to this account.
     *
     * @return list<Role>
     */
    public function assignedRoles(): array
    {
        $raw = is_array($this->roles) ? $this->roles : [];
        if ($this->role !== null && ! in_array($this->role->value, $raw, true)) {
            $raw[] = $this->role->value;
        }

        $result = [];
        foreach (Role::cases() as $case) {
            if (in_array($case->value, $raw, true)) {
                $result[] = $case;
            }
        }

        return $result;
    }

    public function hasAssignedRole(Role ...$roles): bool
    {
        $assigned = $this->assignedRoles();

        foreach ($roles as $role) {
            if (in_array($role, $assigned, true)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAssignedRole(Role::KetuaDls);
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    #[Scope]
    protected function withAssignedRole(Builder $query, Role $role): void
    {
        $query->where(function (Builder $q) use ($role) {
            $q->where('role', $role->value)
                ->orWhereJsonContains('roles', $role->value);
        });
    }
}