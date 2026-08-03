<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SetInitialPasswordNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Ustaw hasło do konta KS Krokus')
            ->greeting('Dzień dobry,')
            ->line('Twój wniosek został zatwierdzony, a konto w serwisie KS Krokus jest już aktywne.')
            ->action('Ustaw własne hasło', $url)
            ->line('Link jest ważny przez 60 minut i może zostać użyty tylko do ustawienia hasła.')
            ->line('Jeśli nie składałeś wniosku, zignoruj tę wiadomość i skontaktuj się z klubem.')
            ->salutation('KS Krokus');
    }
}
