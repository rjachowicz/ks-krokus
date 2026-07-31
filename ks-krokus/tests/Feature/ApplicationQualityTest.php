<?php

declare(strict_types=1);

namespace Tests\Feature;

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
}
