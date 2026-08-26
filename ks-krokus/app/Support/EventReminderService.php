<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\EventReminderSubscription;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EventReminderService
{
    public function updateConsent(User $user, bool $enabled): void
    {
        DB::transaction(function () use ($user, $enabled): void {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->forceFill([
                'event_email_notifications_enabled' => $enabled,
                'event_email_notifications_confirmed_at' => $enabled ? now() : null,
            ])->save();

            if (! $enabled) {
                $lockedUser->eventReminderSubscriptions()->delete();
            }
        });
    }

    public function subscribe(User $user, SportEvent $sportEvent): bool
    {
        $this->assertCanSubscribe($sportEvent);

        return DB::transaction(function () use ($user, $sportEvent): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedEvent = SportEvent::query()
                ->whereKey($sportEvent->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedUser->is_active) {
                throw ValidationException::withMessages([
                    'event_reminder' => 'Przypomnienie może ustawić tylko użytkownik z aktywnym kontem.',
                ]);
            }

            $this->assertCanSubscribe($lockedEvent);

            if (! $lockedUser->event_email_notifications_enabled) {
                $lockedUser->forceFill([
                    'event_email_notifications_enabled' => true,
                    'event_email_notifications_confirmed_at' => now(),
                ])->save();
            }

            $subscription = $lockedUser->eventReminderSubscriptions()
                ->firstOrCreate(
                    ['sport_event_id' => $lockedEvent->getKey()],
                    ['subscribed_at' => now()],
                );

            return $subscription->wasRecentlyCreated;
        });
    }

    public function unsubscribe(User $user, SportEvent $sportEvent): bool
    {
        return DB::transaction(function () use ($user, $sportEvent): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedEvent = SportEvent::query()
                ->whereKey($sportEvent->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            return EventReminderSubscription::query()
                ->where('user_id', $lockedUser->getKey())
                ->where('sport_event_id', $lockedEvent->getKey())
                ->delete() > 0;
        });
    }

    private function assertCanSubscribe(SportEvent $sportEvent): void
    {
        if ($sportEvent->canAcceptEmailReminderSubscriptions()) {
            return;
        }

        throw ValidationException::withMessages([
            'event_reminder' => 'Nie można ustawić przypomnienia dla tego wydarzenia. Zapisy są dostępne tylko dla publicznych, opublikowanych wydarzeń z aktywnymi przypomnieniami, wcześniej niż 24 godziny przed rozpoczęciem.',
        ]);
    }
}
