<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
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

    public function test_tables_and_external_links_expose_accessible_context(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('<th scope="col">', false)
            ->assertSee('aria-labelledby="confirm-dialog-title"', false)
            ->assertSee('aria-describedby="confirm-dialog-message"', false)
            ->assertSee('data-admin-content', false);

        $viewFiles = [
            ...glob(resource_path('views/admin/*/index.blade.php')),
            resource_path('views/admin/dashboard.blade.php'),
            resource_path('views/results/show.blade.php'),
        ];

        foreach ($viewFiles as $viewFile) {
            $contents = (string) file_get_contents($viewFile);

            self::assertDoesNotMatchRegularExpression(
                '/<th\b(?![^>]*\bscope="col")/s',
                $contents,
                $viewFile,
            );
        }

        $externalLinkViews = File::allFiles(resource_path('views'));

        foreach ($externalLinkViews as $viewFile) {
            $contents = (string) file_get_contents($viewFile->getPathname());
            preg_match_all(
                '/<a\b(?=[^>]*target="_blank")[^>]*>/s',
                $contents,
                $externalLinks,
            );

            foreach ($externalLinks[0] as $externalLink) {
                self::assertStringContainsString(
                    'rel="noopener noreferrer"',
                    $externalLink,
                    $viewFile->getPathname(),
                );
                self::assertStringContainsString(
                    'aria-label=',
                    $externalLink,
                    $viewFile->getPathname(),
                );
            }
        }
    }

    public function test_required_labels_and_native_choice_controls_share_one_accessible_pattern(): void
    {
        $formViews = [
            resource_path('views/auth/login.blade.php'),
            resource_path('views/pages/contact.blade.php'),
            resource_path('views/listings/_form.blade.php'),
            resource_path('views/listings/show.blade.php'),
            resource_path('views/admin/competitions/_form.blade.php'),
            resource_path('views/admin/events/_form.blade.php'),
            resource_path('views/admin/positions/_form.blade.php'),
            resource_path('views/admin/posts/_form.blade.php'),
            resource_path('views/admin/results/_form.blade.php'),
            resource_path('views/admin/sale-listings/edit.blade.php'),
            resource_path('views/admin/users/_form.blade.php'),
        ];

        foreach ($formViews as $view) {
            $contents = (string) file_get_contents($view);
            preg_match_all(
                '/<label\b[^>]*>.*?\srequired(?:\s|>).*?<\/label>/s',
                $contents,
                $requiredLabels,
            );

            foreach ($requiredLabels[0] as $requiredLabel) {
                self::assertStringContainsString('form-required', $requiredLabel, $view);
                self::assertStringContainsString('(pole wymagane)', $requiredLabel, $view);
            }
        }

        $postForm = (string) file_get_contents(resource_path('views/admin/posts/_form.blade.php'));
        self::assertMatchesRegularExpression(
            '/<label[^>]*id="post-content-label"[^>]*class="form-label-text"[^>]*>.*?form-required.*?<\/label>/s',
            $postForm,
        );

        $contactForm = (string) file_get_contents(resource_path('views/pages/contact.blade.php'));
        self::assertStringContainsString('Telefon <span class="form-optional">(opcjonalnie)</span>', $contactForm);
        self::assertStringNotContainsString('* - opcjonalne', $contactForm);

        $listingForm = (string) file_get_contents(resource_path('views/listings/_form.blade.php'));
        self::assertStringContainsString('class="listing-existing-images form-choice-group"', $listingForm);
        self::assertStringContainsString('name="primary_image_id"', $listingForm);
        self::assertStringContainsString('id="listing-primary-image-error"', $listingForm);

        $formStyles = (string) file_get_contents(resource_path('css/components/forms.css'));
        self::assertStringContainsString(':not([type="radio"])', $formStyles);
        self::assertStringContainsString('input[type="radio"]', $formStyles);
        self::assertStringContainsString('appearance: none', $formStyles);
        self::assertStringContainsString('input[type="radio"]:checked', $formStyles);
    }
}
