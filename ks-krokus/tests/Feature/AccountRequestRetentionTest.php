<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRequestStatus;
use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountRequestRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_anonymizes_only_rejected_requests_after_retention_period_and_is_idempotent(): void
    {
        config([
            'account_requests.retention_months' => 12,
            'account_requests.retention_action' => 'anonymize',
        ]);
        $reviewer = User::factory()->create();
        $staleRejected = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Rejected,
            'reviewed_by' => $reviewer,
            'reviewed_at' => now()->subMonths(13),
            'rejection_reason' => 'Poufny powód decyzji.',
            'internal_notes' => 'Prywatna notatka administratora.',
            'additional_information' => 'Prywatna treść wniosku.',
        ]);
        $recentRejected = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Rejected,
            'reviewed_at' => now()->subMonths(11),
        ]);
        $pending = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Pending,
            'created_at' => now()->subYears(2),
        ]);
        $approved = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Approved,
            'reviewed_at' => now()->subYears(2),
        ]);

        $this->artisan('account-requests:apply-retention')
            ->expectsOutputToContain('Retencja zakończona')
            ->assertSuccessful();

        $anonymized = $staleRejected->fresh();
        self::assertSame(AccountRequestStatus::Rejected, $anonymized->status);
        self::assertSame('Dane zanonimizowane', $anonymized->fullName());
        self::assertSame('anonimizowany-'.$anonymized->id.'@invalid.local', $anonymized->email);
        self::assertNull($anonymized->rejection_reason);
        self::assertNull($anonymized->internal_notes);
        self::assertNull($anonymized->additional_information);
        self::assertSame($reviewer->id, $anonymized->reviewed_by);
        self::assertNotNull($anonymized->anonymized_at);
        self::assertSame($recentRejected->email, $recentRejected->fresh()->email);
        self::assertSame($pending->email, $pending->fresh()->email);
        self::assertSame($approved->email, $approved->fresh()->email);

        $anonymizedAt = $anonymized->anonymized_at;
        $this->artisan('account-requests:apply-retention')->assertSuccessful();
        self::assertTrue($anonymizedAt->equalTo($staleRejected->fresh()->anonymized_at));
    }

    public function test_delete_action_removes_only_eligible_rejected_requests(): void
    {
        config([
            'account_requests.retention_months' => 6,
            'account_requests.retention_action' => 'delete',
        ]);
        $eligible = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Rejected,
            'reviewed_at' => now()->subMonths(7),
        ]);
        $pending = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Pending,
            'created_at' => now()->subYear(),
        ]);

        $this->artisan('account-requests:apply-retention')->assertSuccessful();

        self::assertDatabaseMissing('account_requests', ['id' => $eligible->id]);
        self::assertDatabaseHas('account_requests', ['id' => $pending->id]);
    }

    public function test_account_request_retention_is_scheduled_in_warsaw_timezone(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('account-requests:apply-retention')
            ->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(static fn ($event): bool => str_contains($event->command, 'account-requests:apply-retention'));

        self::assertNotNull($event);
        self::assertSame('15 2 * * *', $event->expression);
        self::assertSame('Europe/Warsaw', $event->timezone);
    }
}
