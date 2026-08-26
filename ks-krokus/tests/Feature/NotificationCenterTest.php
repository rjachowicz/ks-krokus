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
            ->assertSeeText('Zaznacz wszystkie widoczne')
            ->assertSee('name="notifications[]"', false)
            ->assertSee('data-confirm="Usunąć zaznaczone powiadomienia?', false)
            ->assertSee('data-confirm-dialog', false)
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

    public function test_user_can_delete_an_owned_notification(): void
    {
        $user = User::factory()->create();
        $notification = $this->notification($user, 'Do usunięcia');

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Powiadomienie zostało usunięte.');

        self::assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreign = $this->notification($otherUser, 'Cudze powiadomienie');

        $this->actingAs($owner)
            ->delete(route('notifications.destroy', $foreign))
            ->assertNotFound();

        self::assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }

    public function test_user_can_delete_selected_owned_notifications(): void
    {
        $user = User::factory()->create();
        $first = $this->notification($user, 'Pierwsze');
        $second = $this->notification($user, 'Drugie');
        $left = $this->notification($user, 'Pozostaje');

        $this->actingAs($user)
            ->delete(route('notifications.destroy-selected'), [
                'notifications' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Usunięto wybrane powiadomienia: 2.');

        self::assertDatabaseMissing('notifications', ['id' => $first->id]);
        self::assertDatabaseMissing('notifications', ['id' => $second->id]);
        self::assertDatabaseHas('notifications', ['id' => $left->id]);
    }

    public function test_foreign_id_in_selection_does_not_delete_foreign_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owned = $this->notification($owner, 'Własne');
        $foreign = $this->notification($otherUser, 'Cudze');

        $this->actingAs($owner)
            ->delete(route('notifications.destroy-selected'), [
                'notifications' => [$owned->id, $foreign->id],
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Usunięto wybrane powiadomienia: 1.');

        self::assertDatabaseMissing('notifications', ['id' => $owned->id]);
        self::assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }

    public function test_empty_selection_returns_a_polish_validation_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('notifications.index'))
            ->delete(route('notifications.destroy-selected'), [
                'notifications' => [],
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHasErrors([
                'notifications' => 'Zaznacz co najmniej jedno powiadomienie do usunięcia.',
            ]);
    }

    public function test_selected_notification_identifiers_are_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('notifications.index'))
            ->delete(route('notifications.destroy-selected'), [
                'notifications' => ['nie-jest-uuid'],
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHasErrors([
                'notifications.0' => 'Identyfikator wybranego powiadomienia jest nieprawidłowy.',
            ]);
    }

    public function test_select_visible_fallback_works_without_javascript(): void
    {
        $user = User::factory()->create();
        $first = $this->notification($user, 'Pierwsze');
        $second = $this->notification($user, 'Drugie');
        $left = $this->notification($user, 'Pozostaje');

        $this->actingAs($user)
            ->delete(route('notifications.destroy-selected'), [
                'select_visible' => '1',
                'visible_notifications' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Usunięto wybrane powiadomienia: 2.');

        self::assertDatabaseMissing('notifications', ['id' => $first->id]);
        self::assertDatabaseMissing('notifications', ['id' => $second->id]);
        self::assertDatabaseHas('notifications', ['id' => $left->id]);
    }

    public function test_delete_all_affects_only_current_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->notification($owner, 'Pierwsze');
        $this->notification($owner, 'Drugie');
        $foreign = $this->notification($otherUser, 'Cudze');

        $this->actingAs($owner)
            ->delete(route('notifications.destroy-all'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Usunięto wszystkie Twoje powiadomienia: 2.');

        self::assertSame(0, $owner->notifications()->count());
        self::assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }

    public function test_guest_cannot_access_notification_center_or_delete_endpoints(): void
    {
        $notificationId = (string) Str::uuid();

        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->delete(route('notifications.destroy', $notificationId))->assertRedirect(route('login'));
        $this->delete(route('notifications.destroy-selected'))->assertRedirect(route('login'));
        $this->delete(route('notifications.destroy-all'))->assertRedirect(route('login'));
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
