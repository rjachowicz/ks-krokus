<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\AccountRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class AccountRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AccountRequest $accountRequest)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nowy wniosek o konto',
            'message' => 'Nowy wniosek członka klubu oczekuje na weryfikację.',
            'url' => route('admin.account-requests.show', $this->accountRequest),
            'account_request_id' => $this->accountRequest->getKey(),
        ];
    }
}
