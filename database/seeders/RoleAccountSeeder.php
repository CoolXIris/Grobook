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
            ['email' => 'miwozawazhell@gmail.com'],
            ['name' => 'Ketua DLS', 'role' => Role::KetuaDls, 'email_verified_at' => now()],
        );

        User::updateOrCreate(
            ['email' => 'razhellmiroza@gmail.com'],
            ['name' => 'Petugas DLS', 'role' => Role::PetugasDls, 'email_verified_at' => now()],
        );

        $ketuaTim = User::updateOrCreate(
            ['email' => 'mrjoaster@gmail.com'],
            ['name' => 'Ketua Tim', 'role' => Role::KetuaTim, 'email_verified_at' => now()],
        );

        $team = Team::firstOrCreate(
            ['ketua_tim_id' => $ketuaTim->id],
            ['code' => 'TIM-01', 'name' => 'Tim Penyusun Naskah'],
        );

        if ($team->code === null) {
            $team->update(['code' => 'TIM-01']);
        }

        $ketuaTim->update(['team_id' => $team->id]);
    }
}