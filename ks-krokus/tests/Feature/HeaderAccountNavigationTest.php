<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HeaderAccountNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_actions_in_desktop_and_mobile_navigation(): void
    {
        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="header-login" href="'.route('login').'"', false)
            ->assertSee('class="main-nav__account-link" href="'.route('login').'"', false)
            ->assertSeeText('Zaloguj się')
            ->assertDontSee('data-account-menu', false)
            ->assertDontSee('<span aria-hidden="true">A</span>', false);

        self::assertSame(2, substr_count($response->getContent(), 'Zaloguj się'));
    }

    public function test_every_active_role_sees_its_account_and_can_open_the_shared_panel(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create([
                'name' => 'Konto '.$role->value,
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)
                ->get(route('home'))
                ->assertOk()
                ->assertSee('data-account-menu', false)
                ->assertSee('data-account-menu-toggle', false)
                ->assertSee('aria-controls="account-menu-panel"', false)
                ->assertSee('aria-expanded="false"', false)
                ->assertSee('href="'.route('admin.dashboard').'"', false)
                ->assertSee('method="POST" action="'.route('logout').'"', false)
                ->assertSee('name="_token"', false)
                ->assertSeeText('Panel')
                ->assertSeeText($role->label())
                ->assertDontSee('<span aria-hidden="true">A</span>', false);

            self::assertSame(2, substr_count($response->getContent(), 'method="POST" action="'.route('logout').'"'));

            $this->actingAs($user)
                ->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSeeText($role->label());
        }
    }

    public function test_logout_is_available_only_as_a_csrf_protected_post_action(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/wylogowanie')
            ->assertMethodNotAllowed();

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
