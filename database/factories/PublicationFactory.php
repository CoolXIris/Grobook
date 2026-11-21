<?php

namespace Database\Factories;

use App\Enums\CoverMaker;
use App\Enums\Frequency;
use App\Enums\Language;
use App\Enums\Orientation;
use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Enums\PublishedFor;
use App\Models\ArcPublication;
use App\Models\Publication;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'type' => PublicationType::Arc,
            'arc_publication_id' => ArcPublication::factory(),
            'title' => 'Publikasi '.fake()->words(3, true).' Provinsi Sumatera Selatan',
            'title_en' => 'Publication of '.fake()->words(3, true).' Sumatera Selatan Province',
            'release_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'frequency' => Frequency::Tahunan,
            'language' => Language::Indonesia,
            'status' => PublicationStatus::Diajukan,
            'submitted_at' => now(),
        ];
    }

    public function nonArc(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PublicationType::NonArc,
            'arc_publication_id' => null,
            'year' => (int) date('Y'),
            'pages_romawi' => fake()->randomElement(range(2, 20, 2)),
            'pages_arabic' => fake()->numberBetween(30, 200),
            'has_cooperation' => false,
            'published_for' => PublishedFor::Internal,
            'cover_maker' => CoverMaker::Ipds,
            'orientation' => Orientation::Portrait,
            'paper_size' => 'A4',
        ]);
    }

    public function status(PublicationStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'completed_at' => $status === PublicationStatus::Selesai ? now() : null,
        ]);
    }
}