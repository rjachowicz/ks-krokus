<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PostPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_redirects_to_admin_posts_index(): void
    {
        $this->actingAs($this->moderator())
            ->post(route('admin.posts.store'), [
                'title' => 'Nowa aktualność',
                'content' => 'Treść nowej aktualności.',
                'status' => PublicationStatus::Draft->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.posts.index'));
    }

    public function test_admin_preview_uses_panel_for_published_and_draft_posts(): void
    {
        $moderator = $this->moderator();
        $published = $this->makePost($moderator, PublicationStatus::Published, 'Opublikowana aktualność');
        $draft = $this->makePost($moderator, PublicationStatus::Draft, 'Szkic aktualności');

        foreach ([$published, $draft] as $post) {
            $this->actingAs($moderator)
                ->get(route('admin.posts.show', $post))
                ->assertOk()
                ->assertViewIs('admin.posts.show')
                ->assertSee('class="admin-body"', false)
                ->assertSee('data-admin-sidebar', false)
                ->assertSeeText($post->title)
                ->assertSeeText('Status: '.$post->status->label())
                ->assertSee('href="'.route('admin.posts.edit', $post).'"', false);

            self::assertStringContainsString(
                '/panel/aktualnosci/',
                route('admin.posts.show', $post),
            );
        }
    }

    public function test_preview_links_from_admin_list_use_admin_show_in_the_same_tab(): void
    {
        $moderator = $this->moderator();
        $published = $this->makePost($moderator, PublicationStatus::Published, 'Widoczna aktualność');
        $draft = $this->makePost($moderator, PublicationStatus::Draft, 'Robocza aktualność');

        $this->actingAs($moderator)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('href="'.route('admin.posts.show', $published).'"', false)
            ->assertSee('href="'.route('admin.posts.show', $draft).'"', false)
            ->assertDontSee('href="'.route('news.show', $published).'"', false)
            ->assertDontSee('href="'.route('news.show', $draft).'"', false)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_public_news_show_keeps_public_layout_and_seo_metadata(): void
    {
        $post = $this->makePost(
            $this->moderator(),
            PublicationStatus::Published,
            'Publiczna aktualność',
            'Opis publicznej aktualności.',
        );

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertViewIs('news.show')
            ->assertSee('class="public-body', false)
            ->assertSee('<title>Publiczna aktualność — KS Krokus</title>', false)
            ->assertSee('<meta name="description" content="Opis publicznej aktualności.">', false)
            ->assertSeeText($post->title)
            ->assertDontSee('data-admin-sidebar', false)
            ->assertDontSeeText('Status: Opublikowane');
    }

    public function test_news_cover_and_gallery_use_shared_lightbox_without_new_tabs(): void
    {
        Storage::fake((string) config('media.disk'));
        $post = $this->makePost($this->moderator(), PublicationStatus::Published, 'Aktualność ze zdjęciami');
        $post->update([
            'cover_image_path' => 'news/covers/okladka.jpg',
            'cover_variant_path' => 'news/variants/covers/okladka.jpg',
            'cover_image_alt' => 'Zawodnik na strzelnicy',
        ]);
        $image = $post->images()->create([
            'path' => 'news/gallery/galeria.jpg',
            'alt_text' => 'Drużyna klubowa',
            'caption' => 'Zdjęcie drużynowe',
            'sort_order' => 1,
        ]);
        Storage::disk((string) config('media.disk'))->put($post->cover_image_path, 'okładka');
        Storage::disk((string) config('media.disk'))->put($post->cover_variant_path, 'wariant');
        Storage::disk((string) config('media.disk'))->put($image->path, 'galeria');

        $response = $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('data-media-lightbox="news-lightbox-'.$post->getKey().'"', false)
            ->assertSee('data-media-lightbox-close', false)
            ->assertSee('data-media-lightbox-caption="Zdjęcie drużynowe"', false)
            ->assertDontSee('target="_blank"', false);

        self::assertSame(2, substr_count($response->getContent(), 'data-media-lightbox-trigger='));
    }

    private function moderator(): User
    {
        return User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
    }

    private function makePost(
        User $author,
        PublicationStatus $status,
        string $title,
        ?string $excerpt = null,
    ): Post {
        return Post::query()->create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'excerpt' => $excerpt,
            'content' => 'Treść aktualności.',
            'status' => $status,
            'published_at' => $status === PublicationStatus::Published ? now() : null,
            'author_id' => $author->getKey(),
        ]);
    }
}
