<?php

namespace Database\Factories;

use App\Models\ArcPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArcPublication>
 */
class ArcPublicationFactory extends Factory
{
    public function definition(): array
    {
        $topic = fake()->unique()->words(3, true);

        return [
            'title' => 'Publikasi '.ucwords($topic).' Provinsi Sumatera Selatan',
            'title_en' => 'Publication of '.ucwords($topic).' Sumatera Selatan Province',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}