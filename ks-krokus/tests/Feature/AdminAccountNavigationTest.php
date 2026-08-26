<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminAccountNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_account_routes_require_an_active_authenticated_user(): void
    {
        $this->get(route('admin.account.show'))->assertRedirect(route('login'));
        $this->patch(route('admin.account.profile.update'), [])->assertRedirect(route('login'));
        $this->patch(route('admin.account.email.update'), [])->assertRedirect(route('login'));
        $this->put(route('admin.account.password.update'), [])->assertRedirect(route('login'));

        $inactive = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => false,
        ]);

        $this->actingAs($inactive)
            ->get(route('admin.account.show'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_every_active_role_can_open_own_account_inside_the_panel(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create([
                'name' => 'Konto '.$role->value,
                'role' => $role,
                'is_active' => true,
            ]);

            $this->actingAs($user)
                ->get(route('admin.account.show'))
                ->assertOk()
                ->assertViewIs('account.show')
                ->assertSee('class="admin-shell"', false)
                ->assertDontSee('class="site-header"', false)
                ->assertSee('action="'.route('admin.account.profile.update').'"', false)
                ->assertSee('action="'.route('admin.account.email.update').'"', false)
                ->assertSee('action="'.route('admin.account.password.update').'"', false)
                ->assertSeeText('Konto '.$role->value)
                ->assertSeeText($role->label());
        }
    }

    public function test_panel_topbar_renders_one_accessible_account_menu_and_same_tab_public_link(): void
    {
        $admin = User::factory()->create([
            'name' => 'Jan Administrator',
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-account-menu', false)
            ->assertSee('data-account-menu-toggle', false)
            ->assertSee('aria-controls="admin-account-menu-panel"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="admin-account-menu-panel"', false)
            ->assertSee('href="'.route('admin.account.show').'"', false)
            ->assertSee('href="'.route('notifications.index').'"', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('method="POST" action="'.route('logout').'"', false)
            ->assertSeeText('Jan Administrator')
            ->assertSeeText('Administrator')
            ->assertSeeText('Moje konto')
            ->assertSeeText('Powiadomienia')
            ->assertSeeText('Otwórz stronę')
            ->assertSeeText('Wyloguj się')
            ->assertSee('ui-icon--notification', false)
            ->assertSee('ui-icon--account', false)
            ->assertSee('ui-icon--chevron', false)
            ->assertSee('ui-icon--logout', false)
            ->assertDontSee('target="_blank"', false)
            ->assertDontSee('noopener noreferrer', false)
            ->assertDontSee('●', false)
            ->assertDontSee('◐', false);

        self::assertSame(1, substr_count($response->getContent(), ' data-account-menu>'));
    }

    public function test_existing_account_updates_use_the_same_logic_and_return_to_the_panel(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email' => 'stary-admin@example.com',
            'password' => Hash::make('AktualneHaslo123'),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.account.show'))
            ->patch(route('admin.account.profile.update'), [
                'name' => 'Nowe Imię Administratora',
                'phone' => '+48 600 700 800',
                'show_email_publicly' => '1',
                'show_phone_publicly' => '0',
            ])
            ->assertRedirect(route('admin.account.show'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->from(route('admin.account.show'))
            ->patch(route('admin.account.email.update'), [
                'email' => 'nowy-admin@example.com',
                'email_current_password' => 'AktualneHaslo123',
            ])
            ->assertRedirect(route('admin.account.show'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->from(route('admin.account.show'))
            ->put(route('admin.account.password.update'), [
                'current_password' => 'AktualneHaslo123',
                'password' => 'NoweBezpieczneHaslo123',
                'password_confirmation' => 'NoweBezpieczneHaslo123',
            ])
            ->assertRedirect(route('admin.account.show'))
            ->assertSessionHasNoErrors();

        $admin->refresh();
        self::assertSame('Nowe Imię Administratora', $admin->name);
        self::assertSame('+48 600 700 800', $admin->phone);
        self::assertTrue($admin->show_email_publicly);
        self::assertFalse($admin->show_phone_publicly);
        self::assertSame('nowy-admin@example.com', $admin->email);
        self::assertTrue(Hash::check('NoweBezpieczneHaslo123', $admin->password));
    }
}
