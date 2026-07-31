<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_can_create_published_post(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->post(route('admin.posts.store'), [
                'title' => 'Pierwsza aktualność',
                'excerpt' => 'Krótki opis.',
                'content' => 'Pełna treść aktualności.',
                'status' => PublicationStatus::Published->value,
            ])
            ->assertRedirect();

        $post = Post::query()->firstOrFail();

        self::assertSame('Pierwsza aktualność', $post->title);
        self::assertNotNull($post->published_at);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSeeText('Pierwsza aktualność');
    }

    public function test_rich_text_is_sanitized_before_it_is_saved(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->post(route('admin.posts.store'), [
                'title' => 'Bezpieczna treść',
                'content' => '<h2>Nagłówek</h2><p onclick="alert(1)">Tekst</p><script>alert(1)</script>',
                'content_format' => 'html',
                'status' => PublicationStatus::Published->value,
            ])
            ->assertRedirect();

        $post = Post::query()->firstOrFail();

        self::assertSame('html', $post->content_format);
        self::assertStringContainsString('<h2>Nagłówek</h2>', $post->content);
        self::assertStringNotContainsString('onclick', $post->content);
        self::assertStringNotContainsString('<script', $post->content);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('<h2>Nagłówek</h2>', false);
    }

    public function test_legacy_plain_text_remains_escaped(): void
    {
        $post = Post::query()->create([
            'title' => 'Starsza treść',
            'slug' => 'starsza-tresc',
            'content' => '<script>alert(1)</script>',
            'content_format' => 'plain',
            'status' => PublicationStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
