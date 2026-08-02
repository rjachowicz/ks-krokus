<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SaleListingStatus;
use App\Models\SaleListing;
use App\Notifications\SaleListingExpiringNotification;
use App\Support\SaleListingWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ExpireSaleListings extends Command
{
    protected $signature = 'listings:expire';

    protected $description = 'Wysyła przypomnienia i oznacza przeterminowane ogłoszenia jako wygasłe.';

    public function handle(SaleListingWorkflow $workflow): int
    {
        $reminded = 0;
        $expired = 0;
        $reminderDeadline = now()->addDays((int) config('listings.expiration_reminder_days'));

        SaleListing::query()
            ->where('status', SaleListingStatus::Approved->value)
            ->whereNull('expiration_reminder_sent_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', $reminderDeadline)
            ->with('author')
            ->chunkById(100, function ($listings) use (&$reminded): void {
                foreach ($listings as $listing) {
                    $updated = SaleListing::query()
                        ->whereKey($listing->getKey())
                        ->whereNull('expiration_reminder_sent_at')
                        ->update(['expiration_reminder_sent_at' => now()]);

                    if ($updated === 1) {
                        $listing->refresh();
                        $listing->author->notify(new SaleListingExpiringNotification($listing));
                        $reminded++;
                    }
                }
            });

        SaleListing::query()
            ->where('status', SaleListingStatus::Approved->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($listings) use (&$expired, $workflow): void {
                foreach ($listings as $listing) {
                    DB::transaction(function () use ($listing, &$expired, $workflow): void {
                        $locked = SaleListing::query()
                            ->whereKey($listing->getKey())
                            ->where('status', SaleListingStatus::Approved->value)
                            ->where('expires_at', '<=', now())
                            ->lockForUpdate()
                            ->first();

                        if ($locked !== null) {
                            $workflow->expire($locked);
                            $expired++;
                        }
                    });
                }
            });

        $this->info("Przypomnienia: {$reminded}; wygasłe ogłoszenia: {$expired}.");

        return self::SUCCESS;
    }
}
