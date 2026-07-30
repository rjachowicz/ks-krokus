<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PanelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_moderator_cannot_open_user_management(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_moderator_can_open_content_management(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.posts.index'))
            ->assertOk();

        $this->actingAs($moderator)
            ->get(route('admin.events.index'))
            ->assertOk();

        $this->actingAs($moderator)
            ->get(route('admin.results.index'))
            ->assertOk();
    }

    public function test_regular_user_can_only_open_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.posts.index'))
            ->assertForbidden();
    }
}
