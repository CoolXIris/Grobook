<?php

namespace Database\Factories;

use App\Enums\TimelineAction;
use App\Models\Publication;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'user_id' => User::factory(),
            'action' => TimelineAction::Submitted,
            'description' => null,
            'metadata' => null,
        ];
    }
}