<?php

namespace App\Models;

use Database\Factories\ChecklistSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'position'])]
class ChecklistSection extends Model
{
    /** @use HasFactory<ChecklistSectionFactory> */
    use HasFactory;

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position')->orderBy('id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }
}