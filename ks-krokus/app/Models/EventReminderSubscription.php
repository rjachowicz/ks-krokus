<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EventReminderSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventReminderSubscription extends Model
{
    /** @use HasFactory<EventReminderSubscriptionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'sport_event_id',
        'subscribed_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sportEvent(): BelongsTo
    {
        return $this->belongsTo(SportEvent::class);
    }
}
