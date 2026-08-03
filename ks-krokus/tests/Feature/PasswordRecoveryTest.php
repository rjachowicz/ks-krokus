<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private const NEUTRAL_MESSAGE = 'Jeżeli dla podanego adresu istnieje aktywne konto, wyślemy link do ustawienia nowego hasła.';

    public function test_login_links_to_an_accessible_polish_password_request_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Nie pamiętasz hasła?')
            ->assertSee(route('password.request'), false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Odzyskiwanie hasła')
            ->assertSee('name="email"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_active_user_receives_a_polish_queued_reset_notification_with_absolute_url(): void
    {
        Notification::fake();
        URL::forceRootUrl('https://krokus.example');
        URL::forceScheme('https');
        $user = User::factory()->create(['email' => 'aktywny@example.com', 'is_active' => true]);

        $this->post(route('password.email'), ['email' => ' AKTYWNY@example.com '])
            ->assertRedirect()
            ->assertSessionHas('success', self::NEUTRAL_MESSAGE);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        /** @var ResetPasswordNotification $notification */
        $notification = Notification::sent($user, ResetPasswordNotification::class)->sole();
        $message = $notification->toMail($user);
        parse_str((string) parse_url((string) $message->actionUrl, PHP_URL_QUERY), $query);

        self::assertStringStartsWith('https://krokus.example/ustaw-haslo/', (string) $message->actionUrl);
        self::assertSame('aktywny@example.com', $query['email'] ?? null);
        self::assertSame('Reset hasła do konta KS Krokus', $message->subject);
    }

    public function test_inactive_and_unknown_accounts_receive_the_same_neutral_response_without_notification(): void
    {
        Notification::fake();
        $inactive = User::factory()->create(['email' => 'nieaktywny@example.com', 'is_active' => false]);

        foreach ([$inactive->email, 'brak@example.com'] as $email) {
            $this->post(route('password.email'), ['email' => $email])
                ->assertRedirect()
                ->assertSessionHas('success', self::NEUTRAL_MESSAGE);
        }

        Notification::assertNothingSent();
        self::assertDatabaseMissing('password_reset_tokens', ['email' => $inactive->email]);
        self::assertDatabaseMissing('password_reset_tokens', ['email' => 'brak@example.com']);
    }

    public function test_repeated_request_is_neutral_and_broker_sends_only_one_link_during_throttle_window(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'powtorka@example.com']);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->post(route('password.email'), ['email' => $user->email])
                ->assertSessionHas('success', self::NEUTRAL_MESSAGE);
        }

        Notification::assertSentToTimes($user, ResetPasswordNotification::class, 1);
        self::assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_password_request_endpoint_is_rate_limited(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('password.email'), ['email' => "konto{$attempt}@example.com"])
                ->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'limit@example.com'])
            ->assertTooManyRequests();
    }

    public function test_valid_token_resets_password_and_requires_confirmation(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NoweBezpieczneHaslo123',
            'password_confirmation' => 'inne-haslo',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NoweBezpieczneHaslo123',
            'password_confirmation' => 'NoweBezpieczneHaslo123',
        ])->assertRedirect(route('login'));

        self::assertTrue(Hash::check('NoweBezpieczneHaslo123', $user->fresh()->password));
        self::assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_invalid_expired_and_inactive_account_tokens_are_rejected(): void
    {
        $invalidUser = User::factory()->create(['email' => 'bledny@example.com']);
        $expiredUser = User::factory()->create(['email' => 'wygasly@example.com']);
        $inactiveUser = User::factory()->create(['email' => 'wylaczony@example.com', 'is_active' => false]);
        $expiredToken = Password::broker()->createToken($expiredUser);
        $inactiveToken = Password::broker()->createToken($inactiveUser);
        DB::table('password_reset_tokens')
            ->where('email', $expiredUser->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        foreach ([
            [$invalidUser, 'nieprawidlowy-token'],
            [$expiredUser, $expiredToken],
            [$inactiveUser, $inactiveToken],
        ] as [$user, $token]) {
            $this->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'NoweBezpieczneHaslo123',
                'password_confirmation' => 'NoweBezpieczneHaslo123',
            ])->assertSessionHasErrors([
                'email' => 'Link ustawienia hasła jest nieprawidłowy lub wygasł.',
            ]);

            self::assertFalse(Hash::check('NoweBezpieczneHaslo123', $user->fresh()->password));
        }

        self::assertSame(60, config('auth.passwords.users.expire'));
    }
}
