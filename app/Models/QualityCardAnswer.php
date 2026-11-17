<?php

namespace App\Models;

use App\Enums\ChecklistStatus;
use Database\Factories\QualityCardAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quality_card_id', 'checklist_item_id', 'status', 'note'])]
class QualityCardAnswer extends Model
{
    /** @use HasFactory<QualityCardAnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ChecklistStatus::class,
        ];
    }

    public function qualityCard(): BelongsTo
    {
        return $this->belongsTo(QualityCard::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}