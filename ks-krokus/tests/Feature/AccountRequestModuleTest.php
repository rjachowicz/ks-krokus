<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRequestStatus;
use App\Enums\Discipline;
use App\Enums\UserRole;
use App\Models\AccountRequest;
use App\Models\User;
use App\Notifications\AccountRequestRejectedNotification;
use App\Notifications\AccountRequestSubmittedNotification;
use App\Notifications\SetInitialPasswordNotification;
use App\Support\AccountRequestWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Tests\TestCase;

final class AccountRequestModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_form_links_and_can_submit_request_without_password(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $form = $this->get(route('account-requests.create'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('name="_token"', false)
            ->assertSee('name="pzss_license_number"', false)
            ->assertSee('name="disciplines[]"', false)
            ->assertDontSee('name="password"', false);

        self::assertSame(1, substr_count($form->getContent(), 'name="data_processing_consent"'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="header-account-request" href="'.route('account-requests.create').'"', false);
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Jesteś członkiem klubu i nie masz konta? Złóż wniosek');

        $this->post(route('account-requests.store'), $this->validData())
            ->assertRedirect(route('account-requests.create'))
            ->assertSessionHas(
                'success',
                'Wniosek został przyjęty do weryfikacji. Po zatwierdzeniu otrzymasz wiadomość z instrukcją ustawienia hasła.',
            );

        $accountRequest = AccountRequest::query()->firstOrFail();
        self::assertSame(AccountRequestStatus::Pending, $accountRequest->status);
        self::assertSame('jan@example.com', $accountRequest->email);
        self::assertSame('PZSS-12345', $accountRequest->pzss_license_number);
        Notification::assertSentTo($admin, AccountRequestSubmittedNotification::class);
    }

    public function test_form_has_polish_validation_and_honeypot_protection(): void
    {
        Notification::fake();

        $this->post(route('account-requests.store'), [])
            ->assertSessionHasErrors([
                'first_name' => 'Podaj imię.',
                'last_name' => 'Podaj nazwisko.',
                'email' => 'Podaj adres e-mail.',
                'phone' => 'Podaj numer telefonu.',
                'birth_date' => 'Podaj datę urodzenia.',
                'pzss_license_number' => 'Podaj numer licencji PZSS.',
                'pzss_license_expires_at' => 'Podaj datę ważności licencji PZSS.',
                'patent_number' => 'Podaj numer patentu.',
                'disciplines' => 'Wybierz co najmniej jedną dyscyplinę.',
                'data_processing_consent' => 'Aby złożyć wniosek, zaznacz zgodę na przetwarzanie danych.',
            ]);

        $this->post(route('account-requests.store'), [
            ...$this->validData(),
            'website' => 'https://spam.example.com',
        ])->assertSessionHasErrors('website');

        self::assertDatabaseCount('account_requests', 0);
        Notification::assertNothingSent();
    }

    public function test_duplicate_email_and_license_are_silently_deduplicated(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'jan@example.com']);

        $expectedMessage = 'Wniosek został przyjęty do weryfikacji. Po zatwierdzeniu otrzymasz wiadomość z instrukcją ustawienia hasła.';

        $this->post(route('account-requests.store'), $this->validData())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', $expectedMessage);
        self::assertDatabaseCount('account_requests', 0);

        $existingRequest = AccountRequest::factory()->create([
            'email' => 'inna@example.com',
            'pzss_license_number' => 'PZSS-DUPLIKAT',
        ]);

        $this->post(route('account-requests.store'), [
            ...$this->validData(),
            'email' => 'nowy@example.com',
            'pzss_license_number' => mb_strtolower($existingRequest->pzss_license_number),
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', $expectedMessage);

        self::assertDatabaseCount('account_requests', 1);
        Notification::assertNothingSent();
    }

    public function test_public_submission_is_rate_limited(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->post(route('account-requests.store'), [
                ...$this->validData(),
                'email' => "jan{$attempt}@example.com",
                'pzss_license_number' => "PZSS-{$attempt}",
            ])->assertRedirect();
        }

        $this->post(route('account-requests.store'), [
            ...$this->validData(),
            'email' => 'limit@example.com',
            'pzss_license_number' => 'PZSS-LIMIT',
        ])->assertTooManyRequests();

        self::assertDatabaseCount('account_requests', 3);
    }

    public function test_only_admin_can_open_or_decide_on_account_requests(): void
    {
        $accountRequest = AccountRequest::factory()->create();
        $moderator = User::factory()->create(['role' => UserRole::Moderator]);
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->get(route('admin.account-requests.index'))
            ->assertRedirect(route('login'));

        foreach ([$moderator, $user] as $actor) {
            $this->actingAs($actor)
                ->get(route('admin.account-requests.index'))
                ->assertForbidden();
            $this->actingAs($actor)
                ->get(route('admin.account-requests.show', $accountRequest))
                ->assertForbidden();
            $this->actingAs($actor)
                ->post(route('admin.account-requests.approve', $accountRequest))
                ->assertForbidden();
            $this->actingAs($actor)
                ->post(route('admin.account-requests.reject', $accountRequest), [
                    'rejection_reason' => 'Członkostwo nie zostało potwierdzone.',
                ])
                ->assertForbidden();
        }

        self::assertSame(AccountRequestStatus::Pending, $accountRequest->fresh()->status);
        self::assertDatabaseCount('users', 2);
    }

    public function test_admin_views_show_pending_counter_filters_details_and_notes(): void
    {
        $admin = $this->admin();
        $matching = AccountRequest::factory()->create([
            'first_name' => 'Celina',
            'last_name' => 'Testowa',
            'disciplines' => [Discipline::Rifle->value],
        ]);
        AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Rejected,
            'disciplines' => [Discipline::Pistol->value],
            'reviewed_by' => $admin,
            'reviewed_at' => now(),
            'rejection_reason' => 'Dane nie zostały potwierdzone.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.account-requests.index', [
                'status' => AccountRequestStatus::Pending->value,
                'discipline' => Discipline::Rifle->value,
                'q' => 'Celina',
            ]))
            ->assertOk()
            ->assertSeeText('Celina Testowa')
            ->assertDontSeeText('Dane nie zostały potwierdzone.')
            ->assertSee('oczekujące wnioski: 1', false);

        $this->actingAs($admin)
            ->get(route('admin.account-requests.show', $matching))
            ->assertOk()
            ->assertSeeText('Zatwierdź i utwórz konto')
            ->assertSeeText('Notatki wewnętrzne')
            ->assertSeeText($matching->pzss_license_number);

        $this->actingAs($admin)
            ->put(route('admin.account-requests.notes.update', $matching), [
                'internal_notes' => 'Członkostwo potwierdzone telefonicznie.',
            ])
            ->assertSessionHasNoErrors();

        self::assertSame(
            'Członkostwo potwierdzone telefonicznie.',
            $matching->fresh()->internal_notes,
        );
    }

    public function test_admin_approves_request_creates_active_user_and_sends_password_token(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $accountRequest = AccountRequest::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'nowe-konto@example.com',
            'phone' => '+48 500 111 222',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.account-requests.approve', $accountRequest))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.account-requests.show', $accountRequest));

        $accountRequest->refresh();
        $createdUser = User::query()->findOrFail($accountRequest->created_user_id);

        self::assertSame('Jan Kowalski', $createdUser->name);
        self::assertSame('nowe-konto@example.com', $createdUser->email);
        self::assertSame('+48 500 111 222', $createdUser->phone);
        self::assertSame(UserRole::User, $createdUser->role);
        self::assertTrue($createdUser->is_active);
        self::assertSame(AccountRequestStatus::Approved, $accountRequest->status);
        self::assertSame($admin->id, $accountRequest->reviewed_by);
        self::assertNotNull($accountRequest->reviewed_at);
        self::assertDatabaseHas('password_reset_tokens', ['email' => $createdUser->email]);

        $capturedToken = null;
        Notification::assertSentTo(
            $createdUser,
            SetInitialPasswordNotification::class,
            function (SetInitialPasswordNotification $notification) use (&$capturedToken): bool {
                $capturedToken = $notification->token;

                return $notification->token !== '';
            },
        );

        self::assertIsString($capturedToken);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->get(route('password.reset', ['token' => $capturedToken, 'email' => $createdUser->email]))
            ->assertOk()
            ->assertSeeText('Ustaw własne hasło');

        $this->post(route('password.update'), [
            'token' => $capturedToken,
            'email' => $createdUser->email,
            'password' => 'NoweBezpieczneHaslo123',
            'password_confirmation' => 'NoweBezpieczneHaslo123',
        ])->assertRedirect(route('login'));

        self::assertTrue(Hash::check('NoweBezpieczneHaslo123', $createdUser->fresh()->password));
    }

    public function test_second_approval_does_not_create_duplicate_account(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $accountRequest = AccountRequest::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.account-requests.approve', $accountRequest))
            ->assertSessionHasNoErrors();
        $createdUserId = $accountRequest->fresh()->created_user_id;

        $this->actingAs($admin)
            ->post(route('admin.account-requests.approve', $accountRequest))
            ->assertSessionHasNoErrors();

        self::assertSame($createdUserId, $accountRequest->fresh()->created_user_id);
        self::assertDatabaseCount('users', 2);
        Notification::assertSentToTimes(
            User::query()->findOrFail($createdUserId),
            SetInitialPasswordNotification::class,
            1,
        );
    }

    public function test_approval_rechecks_existing_account_and_rolls_back_when_token_fails(): void
    {
        $admin = $this->admin();
        $duplicate = AccountRequest::factory()->create(['email' => 'duplikat@example.com']);
        User::factory()->create(['email' => 'duplikat@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.account-requests.approve', $duplicate))
            ->assertSessionHasErrors('status');
        self::assertSame(AccountRequestStatus::Pending, $duplicate->fresh()->status);

        $accountRequest = AccountRequest::factory()->create(['email' => 'rollback@example.com']);
        Password::shouldReceive('broker')
            ->once()
            ->andThrow(new RuntimeException('Kontrolowany błąd generowania tokenu.'));

        try {
            app(AccountRequestWorkflow::class)->approve($accountRequest, $admin);
            self::fail('Workflow powinien zgłosić błąd tokenu.');
        } catch (RuntimeException $exception) {
            self::assertSame('Kontrolowany błąd generowania tokenu.', $exception->getMessage());
        }

        self::assertSame(AccountRequestStatus::Pending, $accountRequest->fresh()->status);
        self::assertNull($accountRequest->fresh()->created_user_id);
        self::assertDatabaseMissing('users', ['email' => 'rollback@example.com']);
    }

    public function test_rejection_requires_reason_and_never_sends_internal_notes(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $accountRequest = AccountRequest::factory()->create([
            'email' => 'odrzucony@example.com',
            'internal_notes' => 'Poufna notatka administratora.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.account-requests.reject', $accountRequest))
            ->assertSessionHasErrors('rejection_reason');

        $reason = 'Nie udało się potwierdzić członkostwa w klubie.';
        $this->actingAs($admin)
            ->post(route('admin.account-requests.reject', $accountRequest), [
                'rejection_reason' => $reason,
            ])
            ->assertSessionHasNoErrors();

        $accountRequest->refresh();
        self::assertSame(AccountRequestStatus::Rejected, $accountRequest->status);
        self::assertSame($reason, $accountRequest->rejection_reason);
        self::assertSame($admin->id, $accountRequest->reviewed_by);
        self::assertNotNull($accountRequest->reviewed_at);
        self::assertNull($accountRequest->created_user_id);

        Notification::assertSentOnDemand(
            AccountRequestRejectedNotification::class,
            function (AccountRequestRejectedNotification $notification, array $channels, object $notifiable): bool {
                $message = $notification->toMail($notifiable);
                $content = implode(' ', $message->introLines);

                return in_array('mail', $channels, true)
                    && ! str_contains($content, 'Poufna notatka administratora.')
                    && ! str_contains($content, 'Nie udało się potwierdzić członkostwa');
            },
        );
    }

    /** @return array<string, mixed> */
    private function validData(): array
    {
        return [
            'first_name' => ' Jan ',
            'last_name' => ' Kowalski ',
            'email' => ' JAN@EXAMPLE.COM ',
            'phone' => '+48 500 000 000',
            'birth_date' => '1990-04-12',
            'pzss_license_number' => ' pzss-12345 ',
            'pzss_license_expires_at' => now()->addYear()->toDateString(),
            'patent_number' => 'PAT-12345',
            'firearm_permit_number' => 'POZ-123',
            'member_number' => 'CZ-456',
            'joined_year' => now()->year - 3,
            'disciplines' => [Discipline::Pistol->value, Discipline::Rifle->value],
            'additional_information' => 'Proszę o weryfikację członkostwa.',
            'data_processing_consent' => '1',
        ];
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }
}
