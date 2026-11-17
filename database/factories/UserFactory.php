<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::TimNaskah,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function ketuaTim(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::KetuaTim,
        ]);
    }

    public function petugasDls(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::PetugasDls,
        ]);
    }

    public function ketuaDls(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::KetuaDls,
        ]);
    }

    public function inTeam(?Team $team = null): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team?->id ?? Team::factory(),
        ]);
    }
}