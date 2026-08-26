<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NotificationRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_removes_old_notifications_leaves_fresh_ones_and_is_idempotent(): void
    {
        config(['notifications.retention_days' => 7]);
        $user = User::factory()->create();
        $old = $this->notification($user, now()->subDays(8));
        $fresh = $this->notification($user, now()->subDays(6));

        $this->artisan('notifications:prune')
            ->expectsOutputToContain('zakończona')
            ->assertSuccessful();

        self::assertDatabaseMissing('notifications', ['id' => $old->id]);
        self::assertDatabaseHas('notifications', ['id' => $fresh->id]);

        $this->artisan('notifications:prune')->assertSuccessful();

        self::assertDatabaseCount('notifications', 1);
        self::assertDatabaseHas('notifications', ['id' => $fresh->id]);
    }

    public function test_zero_retention_disables_pruning(): void
    {
        config(['notifications.retention_days' => 0]);
        $user = User::factory()->create();
        $old = $this->notification($user, now()->subYear());

        $this->artisan('notifications:prune')
            ->expectsOutputToContain('wyłączona')
            ->assertSuccessful();

        self::assertDatabaseHas('notifications', ['id' => $old->id]);
    }

    public function test_invalid_retention_configuration_fails_without_deleting_notifications(): void
    {
        config(['notifications.retention_days' => 'nieprawidłowa']);
        $user = User::factory()->create();
        $old = $this->notification($user, now()->subYear());

        $this->artisan('notifications:prune')
            ->expectsOutputToContain('Konfiguracja retencji powiadomień')
            ->assertFailed();

        self::assertDatabaseHas('notifications', ['id' => $old->id]);
    }

    public function test_notification_pruning_is_scheduled_without_overlapping(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('notifications:prune')
            ->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(static fn ($event): bool => str_contains($event->command, 'notifications:prune'));

        self::assertNotNull($event);
        self::assertSame('45 2 * * *', $event->expression);
        self::assertSame('Europe/Warsaw', $event->timezone);
        self::assertTrue($event->withoutOverlapping);
    }

    private function notification(User $user, \DateTimeInterface $createdAt): DatabaseNotification
    {
        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => self::class,
            'data' => [
                'title' => 'Powiadomienie retencyjne',
                'message' => 'Prywatna treść.',
                'url' => route('account.show'),
            ],
            'read_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        return $notification;
    }
}
