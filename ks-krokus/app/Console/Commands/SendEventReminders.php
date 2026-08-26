<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PublicationStatus;
use App\Models\EventReminderSubscription;
use App\Models\SportEvent;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class SendEventReminders extends Command
{
    private const WINDOW_BEFORE_TARGET_MINUTES = 15;

    private const WINDOW_AFTER_TARGET_MINUTES = 15;

    protected $signature = 'events:send-reminders';

    protected $description = 'Kolejkuje jednokrotne przypomnienia około 24 godziny przed wydarzeniem.';

    public function handle(): int
    {
        $runAt = now();
        $windowStart = $runAt->copy()
            ->addHours(24)
            ->subMinutes(self::WINDOW_AFTER_TARGET_MINUTES);
        $windowEnd = $runAt->copy()
            ->addHours(24)
            ->addMinutes(self::WINDOW_BEFORE_TARGET_MINUTES);
        $queued = 0;

        EventReminderSubscription::query()
            ->whereNull('reminder_sent_at')
            ->whereHas('user', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->where('event_email_notifications_enabled', true);
            })
            ->whereHas('sportEvent', function ($query) use ($windowStart, $windowEnd): void {
                $query
                    ->where('status', PublicationStatus::Published->value)
                    ->where('is_public', true)
                    ->where('email_reminders_enabled', true)
                    ->whereBetween('start_at', [$windowStart, $windowEnd]);
            })
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use ($runAt, $windowStart, $windowEnd, &$queued): void {
                foreach ($subscriptions as $subscription) {
                    $wasQueued = DB::transaction(function () use ($subscription, $runAt, $windowStart, $windowEnd): bool {
                        $user = User::query()
                            ->whereKey($subscription->user_id)
                            ->lockForUpdate()
                            ->first();

                        if (
                            $user === null
                            || ! $user->is_active
                            || ! $user->event_email_notifications_enabled
                        ) {
                            return false;
                        }

                        $event = SportEvent::query()
                            ->whereKey($subscription->sport_event_id)
                            ->lockForUpdate()
                            ->first();

                        if (! $this->eventQualifies($event, $windowStart, $windowEnd)) {
                            return false;
                        }

                        $lockedSubscription = EventReminderSubscription::query()
                            ->whereKey($subscription->getKey())
                            ->where('user_id', $user->getKey())
                            ->where('sport_event_id', $event->getKey())
                            ->whereNull('reminder_sent_at')
                            ->lockForUpdate()
                            ->first();

                        if ($lockedSubscription === null) {
                            return false;
                        }

                        $lockedSubscription->forceFill([
                            'reminder_sent_at' => $runAt,
                        ])->save();

                        $user->notify(new EventReminderNotification(
                            subscriptionId: (int) $lockedSubscription->getKey(),
                            eventTitle: $event->title,
                            eventStartAt: $event->start_at->toIso8601String(),
                            eventLocation: (string) $event->location_name,
                            eventSlug: $event->slug,
                        ));

                        return true;
                    });

                    if ($wasQueued) {
                        $queued++;
                    }
                }
            });

        $this->info("Zakolejkowane przypomnienia: {$queued}.");

        return self::SUCCESS;
    }

    private function eventQualifies(
        ?SportEvent $event,
        Carbon $windowStart,
        Carbon $windowEnd,
    ): bool {
        return $event !== null
            && $event->status === PublicationStatus::Published
            && $event->is_public
            && $event->email_reminders_enabled
            && $event->start_at->betweenIncluded($windowStart, $windowEnd);
    }
}
