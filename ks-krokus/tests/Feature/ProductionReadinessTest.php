<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_tables_and_database_worker_timing_are_ready(): void
    {
        self::assertTrue(Schema::hasTable('jobs'));
        self::assertTrue(Schema::hasTable('failed_jobs'));
        self::assertSame(120, config('queue.connections.database.retry_after'));
        self::assertTrue(config('queue.connections.database.after_commit'));
    }

    public function test_example_environment_uses_smtp_and_database_queue_without_secrets(): void
    {
        $environment = file_get_contents(base_path('.env.example'));

        self::assertIsString($environment);
        self::assertStringContainsString("QUEUE_CONNECTION=database\n", $environment);
        self::assertStringContainsString("DB_QUEUE_RETRY_AFTER=120\n", $environment);
        self::assertStringContainsString("MAIL_MAILER=smtp\n", $environment);
        self::assertStringContainsString("MAIL_PORT=587\n", $environment);
        self::assertStringContainsString("MAIL_SCHEME=tls\n", $environment);
        self::assertStringContainsString("MAIL_PASSWORD=\n", $environment);
        self::assertStringContainsString("ADMIN_USER_PASSWORD=\n", $environment);
    }

    public function test_listing_expiration_is_scheduled_in_warsaw_timezone(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('listings:expire')
            ->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(static fn ($event): bool => str_contains($event->command, 'listings:expire'));

        self::assertNotNull($event);
        self::assertSame('15 1 * * *', $event->expression);
        self::assertSame('Europe/Warsaw', $event->timezone);
    }

    public function test_news_and_listings_use_the_same_public_media_disk(): void
    {
        self::assertSame('public', config('content.media_disk'));
        self::assertSame(config('content.media_disk'), config('listings.media_disk'));
        self::assertSame(storage_path('app/public'), config('filesystems.disks.public.root'));
        self::assertStringEndsWith('/storage', config('filesystems.disks.public.url'));
    }

    public function test_admin_seeder_uses_environment_configuration_and_is_idempotent(): void
    {
        config([
            'admin.user.name' => 'Administrator Produkcyjny',
            'admin.user.email' => ' ADMIN@EXAMPLE.COM ',
            'admin.user.password' => 'ProdukcyjneHaslo123',
        ]);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        self::assertSame(1, User::withTrashed()->where('email', 'admin@example.com')->count());
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        self::assertSame('Administrator Produkcyjny', $admin->name);
        self::assertSame(UserRole::Admin, $admin->role);
        self::assertTrue($admin->is_active);
        self::assertTrue(Hash::check('ProdukcyjneHaslo123', $admin->password));
    }

    public function test_admin_seeder_does_not_create_an_account_without_complete_configuration(): void
    {
        config([
            'admin.user.name' => null,
            'admin.user.email' => 'admin@example.com',
            'admin.user.password' => null,
        ]);

        $this->seed(AdminUserSeeder::class);

        self::assertDatabaseCount('users', 0);
    }
}
