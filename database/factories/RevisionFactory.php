<?php

namespace Database\Factories;

use App\Enums\RevisionStage;
use App\Models\Publication;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Revision>
 */
class RevisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'requested_by' => User::factory()->petugasDls(),
            'stage' => RevisionStage::Dls,
            'comment' => fake()->paragraph(),
            'file_path' => null,
            'original_name' => null,
            'resolved_at' => null,
        ];
    }
}