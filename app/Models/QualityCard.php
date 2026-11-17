<?php

namespace App\Models;

use Database\Factories\QualityCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['publication_id', 'user_id', 'pdf_path', 'approved_at'])]
class QualityCard extends Model
{
    /** @use HasFactory<QualityCardFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QualityCardAnswer::class);
    }
}