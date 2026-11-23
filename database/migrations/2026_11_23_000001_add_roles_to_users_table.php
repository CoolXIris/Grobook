<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('role');
        });

        $allRoles = array_map(fn (Role $r) => $r->value, Role::cases());

        DB::table('users')->orderBy('id')->each(function (object $user) use ($allRoles) {
            $roles = $user->role === Role::KetuaDls->value
                ? $allRoles
                : ($user->role ? [$user->role] : []);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['roles' => json_encode($roles)]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
