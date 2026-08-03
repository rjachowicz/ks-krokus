<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AccountRequestRejectedNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct()
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
        return (new MailMessage)
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject('Informacja o wniosku o konto KS Krokus')
            ->greeting('Dzień dobry,')
            ->line('Wniosek o utworzenie konta nie został zatwierdzony.')
            ->line('Jeśli potrzebujesz wyjaśnienia, skontaktuj się z zarządem klubu.')
            ->salutation('KS Krokus');
    }
}
