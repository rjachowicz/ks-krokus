<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class SportEvent extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'event_type',
        'description',
        'start_at',
        'end_at',
        'location_name',
        'address',
        'discipline',
        'competition_system',
        'status',
        'is_public',
        'registration_url',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'discipline' => Discipline::class,
            'competition_system' => CompetitionSystem::class,
            'status' => PublicationStatus::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->where('status', PublicationStatus::Published->value);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function eventCompetitions(): HasMany
    {
        return $this->hasMany(EventCompetition::class);
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(
            CompetitionDefinition::class,
            'event_competitions',
        )->withTimestamps();
    }

    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(
            EventResult::class,
            EventCompetition::class,
            'sport_event_id',
            'event_competition_id',
        );
    }
}
