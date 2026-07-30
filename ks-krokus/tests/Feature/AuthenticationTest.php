<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
            'password' => 'StrongPassword123',
        ]);

        $this->post('/logowanie', [
            'email' => $user->email,
            'password' => 'StrongPassword123',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'password' => 'StrongPassword123',
        ]);

        $this->post('/logowanie', [
            'email' => $user->email,
            'password' => 'StrongPassword123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
