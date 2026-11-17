<?php

namespace Database\Factories;

use App\Enums\ChecklistStatus;
use App\Models\ChecklistItem;
use App\Models\QualityCard;
use App\Models\QualityCardAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityCardAnswer>
 */
class QualityCardAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quality_card_id' => QualityCard::factory(),
            'checklist_item_id' => ChecklistItem::factory(),
            'status' => ChecklistStatus::Sesuai,
            'note' => null,
        ];
    }
}