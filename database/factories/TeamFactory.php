<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Tim '.fake()->unique()->words(2, true),
            'ketua_tim_id' => User::factory()->ketuaTim(),
        ];
    }
}