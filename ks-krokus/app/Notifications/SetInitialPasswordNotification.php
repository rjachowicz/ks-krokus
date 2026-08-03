<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SetInitialPasswordNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $token,
        public readonly bool $accountApproved = true,
    ) {
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

        $message = (new MailMessage)
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject('Ustaw hasło do konta KS Krokus')
            ->greeting('Dzień dobry,');

        $message->line($this->accountApproved
            ? 'Twój wniosek został zatwierdzony, a konto w serwisie KS Krokus jest już aktywne.'
            : 'Administrator KS Krokus przygotował nowy link do ustawienia hasła do Twojego konta.');

        return $message
            ->action('Ustaw własne hasło', $url)
            ->line('Link jest ważny przez '.(int) config('auth.passwords.users.expire', 60).' minut i może zostać użyty tylko raz.')
            ->line($this->accountApproved
                ? 'Jeśli nie składałeś wniosku, zignoruj tę wiadomość i skontaktuj się z klubem.'
                : 'Jeśli nie oczekujesz tej wiadomości, zignoruj ją i skontaktuj się z klubem.')
            ->salutation('KS Krokus');
    }
}
