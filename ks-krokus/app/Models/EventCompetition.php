<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCompetition extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'sport_event_id',
        'competition_definition_id',
        'notes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(SportEvent::class, 'sport_event_id')->withTrashed();
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CompetitionDefinition::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventResult::class)
            ->orderByRaw('place is null')
            ->orderBy('place')
            ->orderBy('id');
    }
}
