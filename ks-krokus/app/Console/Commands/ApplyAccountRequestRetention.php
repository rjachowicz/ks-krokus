<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AccountRequestStatus;
use App\Models\AccountRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApplyAccountRequestRetention extends Command
{
    protected $signature = 'account-requests:apply-retention';

    protected $description = 'Anonimizuje lub usuwa odrzucone wnioski po skonfigurowanym okresie retencji.';

    public function handle(): int
    {
        $months = (int) config('account_requests.retention_months', 12);
        $action = (string) config('account_requests.retention_action', 'anonymize');

        if ($months < 1 || ! in_array($action, ['anonymize', 'delete'], true)) {
            throw ValidationException::withMessages([
                'retention' => 'Konfiguracja retencji wniosków jest nieprawidłowa.',
            ]);
        }

        $cutoff = now()->subMonthsNoOverflow($months);
        $anonymized = 0;
        $deleted = 0;

        AccountRequest::query()
            ->where('status', AccountRequestStatus::Rejected->value)
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<=', $cutoff)
            ->whereNull('anonymized_at')
            ->orderBy('id')
            ->chunkById(100, function ($requests) use ($action, $cutoff, &$anonymized, &$deleted): void {
                foreach ($requests as $request) {
                    DB::transaction(function () use ($request, $action, $cutoff, &$anonymized, &$deleted): void {
                        $locked = AccountRequest::query()
                            ->whereKey($request->getKey())
                            ->where('status', AccountRequestStatus::Rejected->value)
                            ->whereNotNull('reviewed_at')
                            ->where('reviewed_at', '<=', $cutoff)
                            ->whereNull('anonymized_at')
                            ->lockForUpdate()
                            ->first();

                        if ($locked === null) {
                            return;
                        }

                        if ($action === 'delete') {
                            $locked->delete();
                            $deleted++;

                            return;
                        }

                        $this->anonymize($locked);
                        $anonymized++;
                    });
                }
            });

        $this->table(
            ['Próg retencji', 'Zanonimizowane', 'Usunięte'],
            [[$cutoff->toDateString(), $anonymized, $deleted]],
        );
        $this->info('Retencja zakończona. Nie przetwarzano wniosków oczekujących ani zatwierdzonych.');

        return self::SUCCESS;
    }

    private function anonymize(AccountRequest $request): void
    {
        $anonymousKey = (string) $request->getKey();

        $request->forceFill([
            'first_name' => 'Dane',
            'last_name' => 'zanonimizowane',
            'email' => "anonimizowany-{$anonymousKey}@invalid.local",
            'phone' => 'brak',
            'birth_date' => '1970-01-01',
            'pzss_license_number' => "ANON-{$anonymousKey}",
            'pzss_license_expires_at' => '1970-01-01',
            'patent_number' => "ANON-{$anonymousKey}",
            'firearm_permit_number' => null,
            'member_number' => null,
            'joined_year' => null,
            'disciplines' => [],
            'additional_information' => null,
            'data_processing_consent' => false,
            'rejection_reason' => null,
            'internal_notes' => null,
            'created_user_id' => null,
            'anonymized_at' => now(),
        ])->save();
    }
}
