<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SaleListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class SaleListingExpiringNotification extends Notification implements ShouldQueue
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
            'title' => 'Ogłoszenie wkrótce wygaśnie',
            'message' => 'Ogłoszenie „'.$this->listing->title.'” wygaśnie '.$this->listing->expires_at?->format('d.m.Y').'.',
            'url' => route('admin.my-listings.index'),
            'sale_listing_id' => $this->listing->getKey(),
        ];
    }
}
