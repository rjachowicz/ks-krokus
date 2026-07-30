<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ResultStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventResult extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_competition_id',
        'user_id',
        'participant_name',
        'club_name',
        'category',
        'score',
        'place',
        'classification',
        'status',
        'notes',
        'entered_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'place' => 'integer',
            'status' => ResultStatus::class,
        ];
    }

    public function eventCompetition(): BelongsTo
    {
        return $this->belongsTo(EventCompetition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function displayName(): string
    {
        return $this->participant_name;
    }
}
