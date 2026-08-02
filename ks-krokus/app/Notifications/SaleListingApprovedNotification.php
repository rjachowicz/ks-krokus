<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SaleListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class SaleListingApprovedNotification extends Notification implements ShouldQueue
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
            'title' => 'Ogłoszenie zatwierdzone',
            'message' => 'Ogłoszenie „'.$this->listing->title.'” jest już widoczne publicznie.',
            'url' => route('listings.show', $this->listing),
            'sale_listing_id' => $this->listing->getKey(),
        ];
    }
}
