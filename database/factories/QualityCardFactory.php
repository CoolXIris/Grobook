<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\QualityCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityCard>
 */
class QualityCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'user_id' => User::factory()->ketuaTim(),
            'pdf_path' => null,
            'approved_at' => now(),
        ];
    }
}