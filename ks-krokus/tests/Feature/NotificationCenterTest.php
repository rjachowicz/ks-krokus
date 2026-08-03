<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_counter_recent_notifications_and_pagination(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 17) as $number) {
            $this->notification(
                $user,
                sprintf('Powiadomienie %02d', $number),
                $number <= 2,
                $number,
            );
        }

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('nieprzeczytane powiadomienia: 2', false)
            ->assertSee(route('notifications.index'), false);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSeeText('Ostatnie powiadomienia')
            ->assertSeeText('Nieprzeczytane: 2')
            ->assertSeeText('Powiadomienie 17')
            ->assertDontSeeText('Powiadomienie 01')
            ->assertSee('aria-label="Nawigacja stron"', false);
    }

    public function test_user_can_mark_only_an_owned_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owned = $this->notification($owner, 'Własne powiadomienie');
        $foreign = $this->notification($otherUser, 'Cudze powiadomienie');

        $this->actingAs($owner)
            ->patch(route('notifications.read', $owned))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success');

        self::assertNotNull($owned->fresh()->read_at);

        $this->actingAs($owner)
            ->patch(route('notifications.read', $foreign))
            ->assertNotFound();

        self::assertNull($foreign->fresh()->read_at);
    }

    public function test_user_can_mark_all_and_does_not_change_another_users_notifications(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $first = $this->notification($owner, 'Pierwsze');
        $second = $this->notification($owner, 'Drugie');
        $foreign = $this->notification($otherUser, 'Cudze');

        $this->actingAs($owner)
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success');

        self::assertNotNull($first->fresh()->read_at);
        self::assertNotNull($second->fresh()->read_at);
        self::assertNull($foreign->fresh()->read_at);
    }

    public function test_notification_mutations_are_rate_limited_per_user(): void
    {
        $user = User::factory()->create();
        RateLimiter::clear((string) $user->getAuthIdentifier());

        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $this->actingAs($user)
                ->post(route('notifications.read-all'))
                ->assertRedirect(route('notifications.index'));
        }

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertTooManyRequests();
    }

    private function notification(
        User $user,
        string $title,
        bool $unread = true,
        int $createdOffsetSeconds = 0,
    ): DatabaseNotification {
        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => self::class,
            'data' => [
                'title' => $title,
                'message' => 'Treść powiadomienia.',
                'url' => route('account.show'),
            ],
            'read_at' => $unread ? null : now(),
            'created_at' => now()->addSeconds($createdOffsetSeconds),
            'updated_at' => now()->addSeconds($createdOffsetSeconds),
        ]);

        return $notification;
    }
}
