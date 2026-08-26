<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventReminderSubscription;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventReminderSubscription> */
final class EventReminderSubscriptionFactory extends Factory
{
    protected $model = EventReminderSubscription::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sport_event_id' => SportEvent::factory(),
            'subscribed_at' => now(),
            'reminder_sent_at' => null,
        ];
    }
}
