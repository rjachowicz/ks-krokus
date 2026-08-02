<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SaleListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class SaleListingSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SaleListing $listing)
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
            'title' => 'Nowe ogłoszenie do moderacji',
            'message' => 'Ogłoszenie „'.$this->listing->title.'” oczekuje na zatwierdzenie.',
            'url' => route('admin.sale-listings.edit', $this->listing),
            'sale_listing_id' => $this->listing->getKey(),
        ];
    }
}
