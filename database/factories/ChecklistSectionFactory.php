<?php

namespace Database\Factories;

use App\Models\ChecklistSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistSection>
 */
class ChecklistSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->words(2, true)),
            'position' => fake()->numberBetween(1, 20),
        ];
    }
}