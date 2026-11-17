<?php

namespace App\Models;

use App\Enums\CoverMaker;
use App\Enums\Frequency;
use App\Enums\Language;
use App\Enums\Orientation;
use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Enums\PublishedFor;
use App\Enums\Role;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'number', 'user_id', 'team_id', 'type', 'arc_publication_id', 'title', 'title_en',
    'release_date', 'frequency', 'language', 'year', 'pages_romawi', 'pages_arabic',
    'has_cooperation', 'published_for', 'cover_maker', 'orientation', 'paper_size',
    'status', 'submitted_at', 'completed_at',
])]
class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => PublicationType::class,
            'status' => PublicationStatus::class,
            'frequency' => Frequency::class,
            'language' => Language::class,
            'published_for' => PublishedFor::class,
            'cover_maker' => CoverMaker::class,
            'orientation' => Orientation::class,
            'has_cooperation' => 'boolean',
            'release_date' => 'date',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function arcPublication(): BelongsTo
    {
        return $this->belongsTo(ArcPublication::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PublicationFile::class)->orderBy('version');
    }

    public function latestFile(): HasOne
    {
        return $this->hasOne(PublicationFile::class)->latestOfMany('version');
    }

    public function qualityCards(): HasMany
    {
        return $this->hasMany(QualityCard::class);
    }

    public function latestQualityCard(): HasOne
    {
        return $this->hasOne(QualityCard::class)->latestOfMany();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class)->orderBy('created_at');
    }

    public function openRevision(): HasOne
    {
        return $this->hasOne(Revision::class)->whereNull('resolved_at')->latestOfMany();
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class)->orderBy('created_at')->orderBy('id');
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        match ($user->role) {
            Role::TimNaskah => $query->where('user_id', $user->id),
            Role::KetuaTim => $query->where('team_id', $user->team_id),
            default => null,
        };
    }

    #[Scope]
    protected function withStatus(Builder $query, PublicationStatus ...$statuses): void
    {
        $query->whereIn('status', array_map(fn (PublicationStatus $status) => $status->value, $statuses));
    }
}