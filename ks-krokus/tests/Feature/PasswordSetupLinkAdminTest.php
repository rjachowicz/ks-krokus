<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRequestStatus;
use App\Enums\UserRole;
use App\Models\AccountRequest;
use App\Models\User;
use App\Notifications\SetInitialPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordSetupLinkAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_a_new_link_to_existing_active_user_with_audit(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $user = User::factory()->create(['is_active' => true]);
        $oldToken = Password::broker()->createToken($user);

        $this->actingAs($admin)
            ->post(route('admin.users.password.resend', $user))
            ->assertRedirect()
            ->assertSessionHas('success', 'Nowy link ustawienia hasła został wysłany do użytkownika.');

        $user->refresh();
        self::assertSame($admin->id, $user->password_link_sent_by);
        self::assertNotNull($user->password_link_sent_at);
        self::assertFalse(Password::broker()->tokenExists($user, $oldToken));
        Notification::assertSentTo(
            $user,
            SetInitialPasswordNotification::class,
            static fn (SetInitialPasswordNotification $notification): bool => ! $notification->accountApproved
                && Password::broker()->tokenExists($user, $notification->token),
        );
    }

    public function test_admin_can_resend_link_for_an_approved_request_without_creating_another_account(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $user = User::factory()->create();
        $accountRequest = AccountRequest::factory()->create([
            'status' => AccountRequestStatus::Approved,
            'created_user_id' => $user->id,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
        $userCount = User::query()->count();

        $this->actingAs($admin)
            ->get(route('admin.account-requests.show', $accountRequest))
            ->assertOk()
            ->assertSee('Wyślij ponownie link ustawienia hasła')
            ->assertSee('name="_token"', false);

        $this->actingAs($admin)
            ->post(route('admin.account-requests.password.resend', $accountRequest))
            ->assertSessionHas('success', 'Nowy link ustawienia hasła został wysłany do użytkownika.');

        self::assertSame($userCount, User::query()->count());
        Notification::assertSentTo(
            $user,
            SetInitialPasswordNotification::class,
            static fn (SetInitialPasswordNotification $notification): bool => $notification->accountApproved,
        );
    }

    public function test_non_admin_cannot_send_link_and_inactive_user_gets_polish_error(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $target = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->post(route('admin.users.password.resend', $target))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->post(route('admin.users.password.resend', $target))
            ->assertSessionHasErrors([
                'password_link' => 'Link można wysłać wyłącznie dla istniejącego, aktywnego konta.',
            ]);

        Notification::assertNothingSent();
        self::assertNull($target->fresh()->password_link_sent_at);
    }

    public function test_pending_request_cannot_receive_a_password_link(): void
    {
        Notification::fake();
        $accountRequest = AccountRequest::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.account-requests.password.resend', $accountRequest))
            ->assertSessionHasErrors([
                'password_link' => 'Link można wysłać dopiero po zatwierdzeniu wniosku i utworzeniu konta.',
            ]);

        Notification::assertNothingSent();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    }
}
