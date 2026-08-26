<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

final class PruneNotifications extends Command
{
    private const BATCH_SIZE = 500;

    protected $signature = 'notifications:prune';

    protected $description = 'Usuwa bazodanowe powiadomienia po skonfigurowanym okresie retencji.';

    public function handle(): int
    {
        $configuredDays = config('notifications.retention_days', 7);
        $retentionDays = filter_var(
            $configuredDays,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]],
        );

        if ($retentionDays === false) {
            $this->error('Konfiguracja retencji powiadomień musi być nieujemną liczbą całkowitą.');

            return self::FAILURE;
        }

        if ($retentionDays === 0) {
            $this->table(
                ['Retencja (dni)', 'Usunięte', 'Status'],
                [[0, 0, 'wyłączona']],
            );

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($retentionDays);
        $deleted = 0;

        do {
            $ids = DatabaseNotification::query()
                ->where('created_at', '<', $cutoff)
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit(self::BATCH_SIZE)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deletedInBatch = DB::transaction(
                static fn (): int => DatabaseNotification::query()
                    ->whereKey($ids)
                    ->where('created_at', '<', $cutoff)
                    ->delete(),
            );
            $deleted += $deletedInBatch;
        } while ($deletedInBatch > 0);

        $this->table(
            ['Retencja (dni)', 'Próg czasu', 'Usunięte', 'Status'],
            [[$retentionDays, $cutoff->toDateTimeString(), $deleted, 'zakończona']],
        );

        return self::SUCCESS;
    }
}
