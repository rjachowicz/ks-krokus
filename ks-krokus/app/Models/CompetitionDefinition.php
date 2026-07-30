<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionDefinition extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'discipline',
        'competition_system',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'discipline' => Discipline::class,
            'competition_system' => CompetitionSystem::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('competition_system')
            ->orderBy('discipline')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function eventCompetitions(): HasMany
    {
        return $this->hasMany(EventCompetition::class);
    }

    public function label(): string
    {
        return sprintf(
            '%s / %s / %s',
            $this->competition_system->label(),
            $this->discipline->label(),
            $this->name,
        );
    }
}
