<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\PublicationStatus;
use App\Models\EventReminderSubscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

final class EventReminderNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly int $subscriptionId,
        public readonly string $eventTitle,
        public readonly string $eventStartAt,
        public readonly string $eventLocation,
        public readonly string $eventSlug,
    ) {
        // The database queue row and reminder_sent_at must commit atomically.
        $this->beforeCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (
            ! $notifiable instanceof User
            || ! $notifiable->is_active
            || ! $notifiable->event_email_notifications_enabled
        ) {
            return false;
        }

        return EventReminderSubscription::query()
            ->whereKey($this->subscriptionId)
            ->where('user_id', $notifiable->getKey())
            ->whereNotNull('reminder_sent_at')
            ->whereHas('sportEvent', function ($query): void {
                $query
                    ->where('status', PublicationStatus::Published->value)
                    ->where('is_public', true)
                    ->where('email_reminders_enabled', true)
                    ->where('start_at', '>', now());
            })
            ->exists();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $startAt = Carbon::parse($this->eventStartAt)
            ->setTimezone((string) config('app.timezone'));

        return (new MailMessage)
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject('Przypomnienie: '.$this->eventTitle)
            ->greeting('Dzień dobry,')
            ->line('Przypominamy o wydarzeniu „'.$this->eventTitle.'”.')
            ->line('Termin: '.$startAt->format('d.m.Y H:i').'.')
            ->line('Miejsce: '.($this->eventLocation !== '' ? $this->eventLocation : 'nie podano').'.')
            ->action('Otwórz wydarzenie', route('calendar.show', ['slug' => $this->eventSlug]))
            ->line('Otrzymujesz tę wiadomość, ponieważ na swoim koncie KS Krokus ustawiłeś przypomnienie o tym wydarzeniu.')
            ->salutation('KS Krokus');
    }
}
