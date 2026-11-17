<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleAccountSeeder extends Seeder
{
    public function run(): void
    {
        $ketuaDls = User::updateOrCreate(
            ['email' => 'mnursyahputra0@gmail.com'],
            ['name' => 'Ketua DLS', 'role' => Role::KetuaDls, 'email_verified_at' => now()],
        );

        User::updateOrCreate(
            ['email' => 'lightningblue452@gmail.com'],
            ['name' => 'Petugas DLS', 'role' => Role::PetugasDls, 'email_verified_at' => now()],
        );

        $ketuaTim = User::updateOrCreate(
            ['email' => 'yaangga832@gmail.com'],
            ['name' => 'Ketua Tim', 'role' => Role::KetuaTim, 'email_verified_at' => now()],
        );

        $team = Team::firstOrCreate(
            ['ketua_tim_id' => $ketuaTim->id],
            ['name' => 'Tim Penyusun Naskah'],
        );

        $ketuaTim->update(['team_id' => $team->id]);
    }
}