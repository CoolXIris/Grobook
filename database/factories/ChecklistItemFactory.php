<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'checklist_section_id' => ChecklistSection::factory(),
            'number' => fake()->unique()->numberBetween(1, 999),
            'group_label' => null,
            'content' => fake()->sentence(12),
            'position' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}