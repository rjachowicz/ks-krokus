<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $token)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $minutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject('Reset hasła do konta KS Krokus')
            ->greeting('Dzień dobry,')
            ->line('Otrzymaliśmy prośbę o ustawienie nowego hasła do konta KS Krokus.')
            ->action('Ustaw nowe hasło', $url)
            ->line("Link jest ważny przez {$minutes} minut i może zostać użyty tylko raz.")
            ->line('Jeśli nie prosisz o zmianę hasła, zignoruj tę wiadomość.')
            ->salutation('KS Krokus');
    }
}
