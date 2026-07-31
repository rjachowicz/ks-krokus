<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InterfaceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_public_interfaces_render_without_inline_layout_styles(): void
    {
        $routes = [
            route('home'),
            route('news.index'),
            route('calendar.index'),
            route('results.index'),
            route('club'),
            route('contact'),
            route('rodo'),
            route('rules'),
            route('login'),
        ];

        foreach ($routes as $route) {
            $this->get($route)
                ->assertOk()
                ->assertSee('<html lang="pl"', false)
                ->assertSee('<main', false)
                ->assertDontSee('style="', false);
        }
    }

    public function test_mobile_admin_tables_keep_column_context_without_javascript(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('<caption class="sr-only">Lista użytkowników</caption>', false)
            ->assertSee('data-label="Użytkownik"', false)
            ->assertSee('data-label="Operacje"', false);

        $tableViews = [
            resource_path('views/admin/dashboard.blade.php'),
            resource_path('views/admin/posts/index.blade.php'),
            resource_path('views/admin/events/index.blade.php'),
            resource_path('views/admin/results/index.blade.php'),
            resource_path('views/admin/users/index.blade.php'),
            resource_path('views/admin/positions/index.blade.php'),
            resource_path('views/admin/competitions/index.blade.php'),
        ];

        foreach ($tableViews as $view) {
            $contents = (string) file_get_contents($view);

            self::assertStringContainsString('<caption class="sr-only">', $contents, $view);
            self::assertStringContainsString('tabindex="0"', $contents, $view);
            self::assertDoesNotMatchRegularExpression(
                '/<td\b(?![^>]*(?:data-label|colspan))[^>]*>/s',
                $contents,
                $view,
            );
        }
    }
}
