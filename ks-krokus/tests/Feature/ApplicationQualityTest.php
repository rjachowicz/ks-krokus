<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

final class ApplicationQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_lazy_loading_is_blocked_outside_production(): void
    {
        self::assertTrue(Model::preventsLazyLoading());
    }

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $response = $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('Origin-Agent-Cluster', '?1')
            ->assertHeader(
                'Permissions-Policy',
                'camera=(), geolocation=(), microphone=()',
            );

        $contentSecurityPolicy = (string) $response->headers->get(
            'Content-Security-Policy',
        );

        self::assertStringContainsString("default-src 'self'", $contentSecurityPolicy);
        self::assertStringContainsString("object-src 'none'", $contentSecurityPolicy);
        self::assertStringContainsString("form-action 'self'", $contentSecurityPolicy);
        self::assertStringContainsString("script-src-attr 'none'", $contentSecurityPolicy);
        self::assertMatchesRegularExpression(
            "/script-src 'self' 'nonce-[A-Za-z0-9]+'/",
            $contentSecurityPolicy,
        );

        preg_match("/'nonce-([^']+)'/", $contentSecurityPolicy, $nonceMatch);
        $nonce = $nonceMatch[1] ?? null;

        self::assertIsString($nonce);
        $response
            ->assertSee('property="csp-nonce" nonce="'.$nonce.'"', false)
            ->assertSee('<script nonce="'.$nonce.'">', false);
    }

    public function test_account_and_form_pages_are_not_cached_or_indexed(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_production_responses_enable_hsts_and_upgrade_insecure_requests(): void
    {
        $previousEnvironment = $this->app->environment();
        $this->app->instance('env', 'production');

        try {
            $response = $this->get(route('home'))
                ->assertOk()
                ->assertHeader(
                    'Strict-Transport-Security',
                    'max-age=31536000; includeSubDomains',
                );

            self::assertStringContainsString(
                'upgrade-insecure-requests',
                (string) $response->headers->get('Content-Security-Policy'),
            );
        } finally {
            $this->app->instance('env', $previousEnvironment);
        }
    }

    public function test_local_csp_allows_only_the_active_vite_development_origin(): void
    {
        $originalHotFile = Vite::hotFile();
        $temporaryHotFile = storage_path('framework/testing/vite-hot-audit');
        file_put_contents($temporaryHotFile, "http://127.0.0.1:5173\n");
        Vite::useHotFile($temporaryHotFile);

        try {
            $response = $this->get(route('home'))->assertOk();
            $contentSecurityPolicy = (string) $response->headers->get(
                'Content-Security-Policy',
            );

            self::assertStringContainsString(
                'http://127.0.0.1:5173',
                $contentSecurityPolicy,
            );
            self::assertStringContainsString(
                'ws://127.0.0.1:5173',
                $contentSecurityPolicy,
            );
            self::assertStringNotContainsString(' ws: wss:', $contentSecurityPolicy);
        } finally {
            Vite::useHotFile($originalHotFile);

            if (is_file($temporaryHotFile)) {
                unlink($temporaryHotFile);
            }
        }
    }

    public function test_missing_page_uses_polish_error_view(): void
    {
        $this->get('/nie-istnieje')
            ->assertNotFound()
            ->assertSee('Nie znaleziono strony')
            ->assertSee('Wróć na stronę główną');

        $this->view('errors.413')
            ->assertSee('Przesyłane dane są za duże')
            ->assertSee('maksymalnie 6 MB');
    }

    public function test_admin_form_renders_accessible_errors_without_javascript(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->from(route('admin.posts.create'))
            ->followingRedirects()
            ->post(route('admin.posts.store'), [])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('id="post-title-error"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="post-title-error"', false)
            ->assertDontSee('validation.', false);
    }

    public function test_user_email_is_normalized_before_uniqueness_validation(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Powtórzony użytkownik',
                'email' => '  MEMBER@EXAMPLE.COM ',
                'password' => 'BezpieczneHaslo123',
                'password_confirmation' => 'BezpieczneHaslo123',
                'role' => UserRole::User->value,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('email');

        self::assertSame(2, User::query()->count());
    }

    public function test_every_admin_resource_form_keeps_input_and_renders_polish_accessible_errors(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        $forms = [
            [
                route('admin.posts.create'),
                route('admin.posts.store'),
                [
                    'title' => 'Zachowana aktualność',
                    'content' => '',
                    'status' => PublicationStatus::Draft->value,
                ],
                'post-content-error',
                'Zachowana aktualność',
            ],
            [
                route('admin.events.create'),
                route('admin.events.store'),
                ['title' => 'Zachowane wydarzenie'],
                'event-type-error',
                'Zachowane wydarzenie',
            ],
            [
                route('admin.results.create'),
                route('admin.results.store'),
                ['participant_name' => 'Zachowany zawodnik'],
                'result-event-competition-error',
                'Zachowany zawodnik',
            ],
            [
                route('admin.users.create'),
                route('admin.users.store'),
                ['name' => 'Zachowany użytkownik'],
                'user-email-error',
                'Zachowany użytkownik',
            ],
            [
                route('admin.positions.create'),
                route('admin.positions.store'),
                ['name' => 'Zachowana funkcja'],
                'position-order-error',
                'Zachowana funkcja',
            ],
            [
                route('admin.competitions.create'),
                route('admin.competitions.store'),
                ['name' => 'Zachowana konkurencja'],
                'competition-code-error',
                'Zachowana konkurencja',
            ],
        ];

        foreach ($forms as [$formUrl, $submitUrl, $payload, $errorId, $oldValue]) {
            $response = $this->actingAs($admin)
                ->from($formUrl)
                ->followingRedirects()
                ->post($submitUrl, $payload)
                ->assertOk()
                ->assertSee('role="alert"', false)
                ->assertSee('aria-invalid="true"', false)
                ->assertSee("id=\"{$errorId}\"", false)
                ->assertSee($oldValue);

            $content = $response->getContent();

            self::assertStringNotContainsString('validation.', $content);
            self::assertStringNotContainsString('The ', $content);
            self::assertStringNotContainsString(' must ', $content);
        }
    }

    public function test_malformed_array_values_return_form_errors_instead_of_breaking_the_form(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->followingRedirects()
            ->post(route('admin.events.store'), [
                'title' => ['nieprawidłowa wartość'],
                'event_type' => ['competition'],
                'start_at' => ['2026-08-01 10:00:00'],
                'location_name' => ['Strzelnica'],
                'status' => ['draft'],
            ])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertDontSee('validation.', false);
    }

    public function test_admin_filter_errors_are_polish_and_connected_to_controls(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        $filters = [
            [route('admin.posts.index'), ['status' => 'unknown'], 'post-filter-status-error'],
            [route('admin.events.index'), ['event_type' => 'unknown'], 'event-filter-type-error'],
            [route('admin.results.index'), ['event_id' => 'unknown'], 'result-filter-event-error'],
            [route('admin.users.index'), ['active' => 'unknown'], 'user-filter-active-error'],
            [route('admin.competitions.index'), ['discipline' => 'unknown'], 'competition-filter-discipline-error'],
        ];

        foreach ($filters as [$url, $query, $errorId]) {
            $response = $this->actingAs($admin)
                ->from($url)
                ->followingRedirects()
                ->get($url.'?'.http_build_query($query))
                ->assertOk()
                ->assertSee('role="alert"', false)
                ->assertSee('aria-invalid="true"', false)
                ->assertSee("id=\"{$errorId}\"", false);

            self::assertStringNotContainsString(
                'validation.',
                $response->getContent(),
            );
        }
    }

    public function test_public_filter_errors_are_connected_to_controls(): void
    {
        $filters = [
            [route('news.index'), ['q' => str_repeat('x', 101)], 'news-filter-query-error'],
            [route('calendar.index'), ['event_type' => 'unknown'], 'calendar-filter-type-error'],
            [route('results.index'), ['discipline' => 'unknown'], 'results-filter-discipline-error'],
        ];

        foreach ($filters as [$url, $query, $errorId]) {
            $response = $this->from($url)
                ->followingRedirects()
                ->get($url.'?'.http_build_query($query))
                ->assertOk()
                ->assertSee('role="alert"', false)
                ->assertSee('aria-invalid="true"', false)
                ->assertSee("id=\"{$errorId}\"", false);

            self::assertStringNotContainsString(
                'validation.',
                $response->getContent(),
            );
        }
    }
}
