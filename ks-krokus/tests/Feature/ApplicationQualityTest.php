<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApplicationQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader(
                'Permissions-Policy',
                'camera=(), geolocation=(), microphone=()',
            );
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
}
