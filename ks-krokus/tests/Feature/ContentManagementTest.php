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
}
