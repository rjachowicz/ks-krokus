<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\EventReminderSubscription;
use App\Models\SportEvent;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class EventReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-10 10:00:00');
    }

    public function test_event_email_consent_is_disabled_by_default(): void
    {
        $user = User::factory()->create();

        self::assertFalse($user->event_email_notifications_enabled);
        self::assertNull($user->event_email_notifications_confirmed_at);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSeeText('Chcę otrzymywać e-mailowe przypomnienia o wybranych wydarzeniach');
    }

    public function test_enabling_consent_requires_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AktualneHaslo123'),
        ]);

        $this->actingAs($user)
            ->patch(route('account.event-notifications.update'), [
                'event_email_notifications_enabled' => '1',
            ])
            ->assertSessionHasErrors('event_notifications_current_password');

        $this->actingAs($user)
            ->patch(route('account.event-notifications.update'), [
                'event_email_notifications_enabled' => '1',
                'event_notifications_current_password' => 'BledneHaslo123',
            ])
            ->assertSessionHasErrors('event_notifications_current_password');

        self::assertFalse($user->fresh()->event_email_notifications_enabled);
        self::assertNull($user->fresh()->event_email_notifications_confirmed_at);
        self::assertArrayNotHasKey(
            'event_notifications_current_password',
            session()->getOldInput(),
        );

        $this->actingAs($user)
            ->patch(route('account.event-notifications.update'), [
                'event_email_notifications_enabled' => '1',
                'event_notifications_current_password' => 'AktualneHaslo123',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'E-mailowe przypomnienia o wydarzeniach zostały włączone.');

        self::assertTrue($user->fresh()->event_email_notifications_enabled);
        self::assertNotNull($user->fresh()->event_email_notifications_confirmed_at);
    }

    public function test_disabling_consent_needs_no_password_and_removes_subscriptions(): void
    {
        $user = User::factory()->create([
            'event_email_notifications_enabled' => true,
            'event_email_notifications_confirmed_at' => now()->subDay(),
        ]);
        EventReminderSubscription::factory()->create([
            'user_id' => $user,
            'sport_event_id' => $this->eligibleEvent(),
        ]);

        $this->actingAs($user)
            ->patch(route('account.event-notifications.update'), [
                'event_email_notifications_enabled' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'E-mailowe przypomnienia zostały wyłączone, a zapisane przypomnienia anulowane.');

        $user->refresh();
        self::assertFalse($user->event_email_notifications_enabled);
        self::assertNull($user->event_email_notifications_confirmed_at);
        self::assertSame(0, $user->eventReminderSubscriptions()->count());
    }

    public function test_subscribing_requires_password_and_can_atomically_enable_consent(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AktualneHaslo123'),
            'event_email_notifications_enabled' => false,
        ]);
        $event = $this->eligibleEvent();

        $this->actingAs($user)
            ->post(route('event-reminders.store', $event), [
                'event_reminder_current_password' => 'BledneHaslo123',
            ])
            ->assertRedirect(route('calendar.show', $event))
            ->assertSessionHasErrors('event_reminder_current_password');

        self::assertFalse($user->fresh()->event_email_notifications_enabled);
        self::assertDatabaseCount('event_reminder_subscriptions', 0);
        self::assertArrayNotHasKey(
            'event_reminder_current_password',
            session()->getOldInput(),
        );

        $this->actingAs($user)
            ->post(route('event-reminders.store', $event), [
                'event_reminder_current_password' => 'AktualneHaslo123',
            ])
            ->assertRedirect(route('calendar.show', $event))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Przypomnienie o wydarzeniu zostało ustawione.');

        $user->refresh();
        self::assertTrue($user->event_email_notifications_enabled);
        self::assertNotNull($user->event_email_notifications_confirmed_at);
        $this->assertDatabaseHas('event_reminder_subscriptions', [
            'user_id' => $user->id,
            'sport_event_id' => $event->id,
        ]);
    }

    public function test_subscription_closes_exactly_twenty_four_hours_before_start(): void
    {
        $user = $this->consentingUser();
        $event = $this->eligibleEvent([
            'start_at' => now()->addHours(24),
            'end_at' => now()->addHours(26),
        ]);

        self::assertFalse($event->canAcceptEmailReminderSubscriptions());

        $this->actingAs($user)
            ->get(route('calendar.show', $event))
            ->assertOk()
            ->assertSeeText('Zapisy na przypomnienie są zamknięte dokładnie 24 godziny')
            ->assertDontSeeText('Przypomnij mi o wydarzeniu');

        $this->actingAs($user)
            ->post(route('event-reminders.store', $event), [
                'event_reminder_current_password' => 'AktualneHaslo123',
            ])
            ->assertSessionHasErrors('event_reminder');

        self::assertDatabaseCount('event_reminder_subscriptions', 0);
    }

    public function test_draft_archived_and_non_public_events_reject_subscriptions_without_enabling_consent(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AktualneHaslo123'),
            'event_email_notifications_enabled' => false,
        ]);
        $events = [
            $this->eligibleEvent(['slug' => 'draft-event', 'status' => PublicationStatus::Draft]),
            $this->eligibleEvent(['slug' => 'archived-event', 'status' => PublicationStatus::Archived]),
            $this->eligibleEvent(['slug' => 'private-event', 'is_public' => false]),
        ];

        foreach ($events as $event) {
            $this->actingAs($user)
                ->post(route('event-reminders.store', $event), [
                    'event_reminder_current_password' => 'AktualneHaslo123',
                ])
                ->assertSessionHasErrors('event_reminder');
        }

        self::assertFalse($user->fresh()->event_email_notifications_enabled);
        self::assertDatabaseCount('event_reminder_subscriptions', 0);
    }

    public function test_duplicate_subscription_is_idempotent(): void
    {
        $user = $this->consentingUser();
        $event = $this->eligibleEvent();
        $payload = ['event_reminder_current_password' => 'AktualneHaslo123'];

        $this->actingAs($user)
            ->post(route('event-reminders.store', $event), $payload)
            ->assertSessionHas('success', 'Przypomnienie o wydarzeniu zostało ustawione.');
        $this->actingAs($user)
            ->post(route('event-reminders.store', $event), $payload)
            ->assertSessionHas('success', 'Przypomnienie o tym wydarzeniu jest już ustawione.');

        self::assertSame(1, EventReminderSubscription::query()->count());
    }

    public function test_user_can_cancel_only_own_subscription(): void
    {
        $owner = $this->consentingUser();
        $other = $this->consentingUser(['email' => 'other@example.test']);
        $event = $this->eligibleEvent();
        $subscription = EventReminderSubscription::factory()->create([
            'user_id' => $owner,
            'sport_event_id' => $event,
        ]);

        $this->actingAs($owner)
            ->get(route('calendar.show', $event))
            ->assertOk()
            ->assertSeeText('Anuluj przypomnienie');

        $this->actingAs($other)
            ->delete(route('event-reminders.destroy', $event))
            ->assertRedirect(route('calendar.show', $event));
        self::assertNotNull($subscription->fresh());

        $this->actingAs($owner)
            ->delete(route('event-reminders.destroy', $event))
            ->assertSessionHas('success', 'Przypomnienie o wydarzeniu zostało anulowane.');
        self::assertNull($subscription->fresh());

        $this->actingAs($owner)
            ->delete(route('event-reminders.destroy', $event))
            ->assertSessionHasNoErrors();
    }

    public function test_guest_and_inactive_user_cannot_change_reminders(): void
    {
        $event = $this->eligibleEvent();

        $this->post(route('event-reminders.store', $event), [])
            ->assertRedirect(route('login'));
        $this->patch(route('account.event-notifications.update'), [])
            ->assertRedirect(route('login'));

        $inactive = $this->consentingUser([
            'email' => 'blocked-reminders@example.test',
            'is_active' => false,
        ]);

        $this->actingAs($inactive)
            ->post(route('event-reminders.store', $event), [
                'event_reminder_current_password' => 'AktualneHaslo123',
            ])
            ->assertRedirect(route('login'));

        self::assertDatabaseCount('event_reminder_subscriptions', 0);
    }

    public function test_admin_can_enable_event_reminders_through_validated_form(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSeeText('Włącz e-mailowe przypomnienia');

        $this->actingAs($moderator)
            ->post(route('admin.events.store'), [
                'title' => 'Wydarzenie z przypomnieniem',
                'event_type' => EventType::Competition->value,
                'start_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'location_name' => 'Strzelnica',
                'status' => PublicationStatus::Published->value,
                'is_public' => '1',
                'email_reminders_enabled' => '1',
            ])
            ->assertSessionHasNoErrors();

        $event = SportEvent::query()->firstOrFail();
        self::assertTrue($event->email_reminders_enabled);
    }

    public function test_disabling_reminders_on_event_removes_its_subscriptions(): void
    {
        $moderator = User::factory()->create(['role' => UserRole::Moderator]);
        $event = $this->eligibleEvent();
        EventReminderSubscription::factory()->create([
            'user_id' => $this->consentingUser(),
            'sport_event_id' => $event,
        ]);

        $this->actingAs($moderator)
            ->put(route('admin.events.update', $event), [
                'title' => $event->title,
                'event_type' => $event->event_type->value,
                'start_at' => $event->start_at->format('Y-m-d H:i:s'),
                'end_at' => $event->end_at?->format('Y-m-d H:i:s'),
                'location_name' => $event->location_name,
                'status' => $event->status->value,
                'is_public' => '1',
                'email_reminders_enabled' => '0',
            ])
            ->assertSessionHasNoErrors();

        self::assertFalse($event->fresh()->email_reminders_enabled);
        self::assertDatabaseCount('event_reminder_subscriptions', 0);
    }

    public function test_command_queues_reminder_once_and_marks_subscription_before_dispatch(): void
    {
        Notification::fake();
        $user = $this->consentingUser();
        $event = $this->eligibleEvent([
            'start_at' => now()->addHours(24),
            'end_at' => now()->addHours(26),
        ]);
        $subscription = EventReminderSubscription::factory()->create([
            'user_id' => $user,
            'sport_event_id' => $event,
        ]);

        $this->artisan('events:send-reminders')
            ->expectsOutput('Zakolejkowane przypomnienia: 1.')
            ->assertSuccessful();
        $this->artisan('events:send-reminders')
            ->expectsOutput('Zakolejkowane przypomnienia: 0.')
            ->assertSuccessful();

        self::assertNotNull($subscription->fresh()->reminder_sent_at);
        Notification::assertSentToTimes($user, EventReminderNotification::class, 1);
    }

    public function test_database_queue_job_and_marker_are_created_together(): void
    {
        config()->set('queue.default', 'database');

        $user = $this->consentingUser();
        $event = $this->eligibleEvent([
            'start_at' => now()->addHours(24),
            'end_at' => now()->addHours(26),
        ]);
        $subscription = EventReminderSubscription::factory()->create([
            'user_id' => $user,
            'sport_event_id' => $event,
        ]);

        $this->artisan('events:send-reminders')->assertSuccessful();

        self::assertNotNull($subscription->fresh()->reminder_sent_at);
        $this->assertDatabaseCount('jobs', 1);
        self::assertStringNotContainsString(
            $event->title,
            (string) DB::table('jobs')->value('payload'),
        );
    }

    public function test_command_skips_ineligible_events_users_and_consents(): void
    {
        Notification::fake();
        $active = $this->consentingUser();
        $inactive = $this->consentingUser([
            'email' => 'inactive@example.test',
            'is_active' => false,
        ]);
        $withoutConsent = User::factory()->create([
            'email' => 'no-consent@example.test',
            'event_email_notifications_enabled' => false,
        ]);
        $validTime = [
            'start_at' => now()->addHours(24),
            'end_at' => now()->addHours(26),
        ];
        $events = [
            $this->eligibleEvent([...$validTime, 'slug' => 'draft-command', 'status' => PublicationStatus::Draft]),
            $this->eligibleEvent([...$validTime, 'slug' => 'archived-command', 'status' => PublicationStatus::Archived]),
            $this->eligibleEvent([...$validTime, 'slug' => 'private-command', 'is_public' => false]),
            $this->eligibleEvent([...$validTime, 'slug' => 'disabled-command', 'email_reminders_enabled' => false]),
        ];
        $deleted = $this->eligibleEvent([...$validTime, 'slug' => 'deleted-command']);
        $deleted->delete();

        foreach ([...$events, $deleted] as $event) {
            EventReminderSubscription::factory()->create([
                'user_id' => $active,
                'sport_event_id' => $event,
            ]);
        }

        $eligibleForInactive = $this->eligibleEvent([...$validTime, 'slug' => 'inactive-user-command']);
        EventReminderSubscription::factory()->create([
            'user_id' => $inactive,
            'sport_event_id' => $eligibleForInactive,
        ]);
        $eligibleWithoutConsent = $this->eligibleEvent([...$validTime, 'slug' => 'no-consent-command']);
        EventReminderSubscription::factory()->create([
            'user_id' => $withoutConsent,
            'sport_event_id' => $eligibleWithoutConsent,
        ]);

        $this->artisan('events:send-reminders')
            ->expectsOutput('Zakolejkowane przypomnienia: 0.')
            ->assertSuccessful();

        Notification::assertNothingSent();
        self::assertSame(0, EventReminderSubscription::query()->whereNotNull('reminder_sent_at')->count());
    }

    public function test_notification_is_queued_and_contains_event_context(): void
    {
        $notification = new EventReminderNotification(
            subscriptionId: 123,
            eventTitle: 'Puchar Krokusa',
            eventStartAt: '2026-09-11T10:00:00+02:00',
            eventLocation: 'Strzelnica KS Krokus',
            eventSlug: 'puchar-krokusa',
        );
        $mail = $notification->toMail($this->consentingUser());

        self::assertInstanceOf(ShouldQueue::class, $notification);
        self::assertInstanceOf(ShouldBeEncrypted::class, $notification);
        self::assertFalse($notification->afterCommit);
        self::assertSame('Przypomnienie: Puchar Krokusa', $mail->subject);
        self::assertContains('Termin: 11.09.2026 10:00.', $mail->introLines);
        self::assertContains('Miejsce: Strzelnica KS Krokus.', $mail->introLines);
        self::assertSame(route('calendar.show', ['slug' => 'puchar-krokusa']), $mail->actionUrl);
        self::assertStringContainsString(
            'ponieważ',
            implode(' ', [...$mail->introLines, ...$mail->outroLines]),
        );
    }

    public function test_scheduler_runs_event_reminders_every_five_minutes_in_warsaw_without_overlap(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('events:send-reminders')
            ->assertSuccessful();

        $event = collect(app(Schedule::class)->events())
            ->first(static fn ($event): bool => str_contains($event->command, 'events:send-reminders'));

        self::assertNotNull($event);
        self::assertSame('*/5 * * * *', $event->expression);
        self::assertSame('Europe/Warsaw', $event->timezone);
        self::assertTrue($event->withoutOverlapping);
    }

    public function test_subscription_factory_provides_casts_and_relations(): void
    {
        $subscription = EventReminderSubscription::factory()->create();

        self::assertInstanceOf(User::class, $subscription->user);
        self::assertInstanceOf(SportEvent::class, $subscription->sportEvent);
        self::assertNotNull($subscription->subscribed_at);
        self::assertNull($subscription->reminder_sent_at);
        self::assertSame(
            $subscription->getKey(),
            $subscription->user->eventReminderSubscriptions()->firstOrFail()->getKey(),
        );
    }

    /** @param array<string, mixed> $attributes */
    private function consentingUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('AktualneHaslo123'),
            'event_email_notifications_enabled' => true,
            'event_email_notifications_confirmed_at' => now()->subDay(),
        ], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    private function eligibleEvent(array $attributes = []): SportEvent
    {
        return SportEvent::factory()->create(array_merge([
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHours(2),
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'email_reminders_enabled' => true,
        ], $attributes));
    }
}
