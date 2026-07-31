<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\UserRole;
use App\Models\ClubPosition;
use App\Models\CompetitionDefinition;
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
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-confirm-dialog', false);
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

    public function test_moderator_is_forbidden_from_every_admin_only_crud_action(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $editedUser = User::factory()->create();
        $position = ClubPosition::query()->create([
            'name' => 'Funkcja testowa',
            'slug' => 'funkcja-testowa',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $definition = CompetitionDefinition::query()->create([
            'code' => 'AUTH-TEST',
            'name' => 'Konkurencja testowa',
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $requests = [
            ['GET', route('admin.users.index')],
            ['GET', route('admin.users.create')],
            ['POST', route('admin.users.store')],
            ['GET', route('admin.users.edit', $editedUser)],
            ['PUT', route('admin.users.update', $editedUser)],
            ['DELETE', route('admin.users.destroy', $editedUser)],
            ['GET', route('admin.positions.index')],
            ['GET', route('admin.positions.create')],
            ['POST', route('admin.positions.store')],
            ['GET', route('admin.positions.edit', $position)],
            ['PUT', route('admin.positions.update', $position)],
            ['DELETE', route('admin.positions.destroy', $position)],
            ['GET', route('admin.competitions.index')],
            ['GET', route('admin.competitions.create')],
            ['POST', route('admin.competitions.store')],
            ['GET', route('admin.competitions.edit', $definition)],
            ['PUT', route('admin.competitions.update', $definition)],
            ['DELETE', route('admin.competitions.destroy', $definition)],
        ];

        $this->actingAs($moderator);

        foreach ($requests as [$method, $uri]) {
            $this->call($method, $uri)->assertForbidden();
        }

        self::assertNotNull($editedUser->fresh());
        self::assertNotNull($position->fresh());
        self::assertNotNull($definition->fresh());
    }
}
