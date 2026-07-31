<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

final class PostUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_can_be_added_and_replaced_without_leaving_old_file(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->post(route('admin.posts.store'), [
                'title' => 'Aktualność ze zdjęciem',
                'content' => 'Treść',
                'status' => PublicationStatus::Draft->value,
                'cover_image' => UploadedFile::fake()->image('pierwsze.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $post = Post::query()->firstOrFail();
        $oldPath = $post->cover_image_path;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'cover_image' => UploadedFile::fake()->image('drugie.png'),
            ])
            ->assertSessionHasNoErrors();

        $post->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($post->cover_image_path);
    }

    public function test_gallery_files_can_be_added_and_removed(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $this->actingAs($moderator)
            ->post(route('admin.posts.store'), [
                'title' => 'Galeria',
                'content' => 'Treść',
                'status' => PublicationStatus::Draft->value,
                'gallery_images' => [
                    UploadedFile::fake()->image('pierwsze.jpg'),
                    UploadedFile::fake()->image('drugie.webp'),
                ],
            ])
            ->assertSessionHasNoErrors();

        $post = Post::query()->firstOrFail();
        $image = $post->images()->firstOrFail();
        Storage::disk('public')->assertExists($image->path);

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'delete_images' => [$image->id],
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($image->path);
        self::assertDatabaseMissing('post_images', ['id' => $image->id]);
    }

    public function test_invalid_and_oversized_images_have_polish_errors(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $response = $this->actingAs($moderator)
            ->post(route('admin.posts.store'), [
                'title' => 'Błędne zdjęcie',
                'content' => 'Treść',
                'status' => PublicationStatus::Draft->value,
                'cover_image' => UploadedFile::fake()->create('dokument.pdf', 20, 'application/pdf'),
                'gallery_images' => [
                    UploadedFile::fake()->create('duze.jpg', 7000, 'image/jpeg'),
                ],
            ]);

        $response->assertSessionHasErrors(['cover_image', 'gallery_images.0']);

        foreach (session('errors')->all() as $message) {
            self::assertStringNotContainsString('validation.', $message);
        }
    }

    public function test_new_files_are_removed_when_database_transaction_fails(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        Post::creating(static function (): never {
            throw new RuntimeException('Symulowany błąd bazy.');
        });

        try {
            $this->withoutExceptionHandling()
                ->actingAs($moderator)
                ->post(route('admin.posts.store'), [
                    'title' => 'Nieudany zapis',
                    'content' => 'Treść',
                    'status' => PublicationStatus::Draft->value,
                    'cover_image' => UploadedFile::fake()->image('okladka.jpg'),
                    'gallery_images' => [UploadedFile::fake()->image('galeria.jpg')],
                ]);

            self::fail('Oczekiwano wyjątku zapisu.');
        } catch (RuntimeException $exception) {
            self::assertSame('Symulowany błąd bazy.', $exception->getMessage());
        } finally {
            Post::flushEventListeners();
        }

        self::assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_gallery_cannot_exceed_twelve_images(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Pełna galeria',
            'slug' => 'pelna-galeria',
            'content' => 'Treść',
            'status' => PublicationStatus::Draft,
        ]);

        foreach (range(1, 12) as $number) {
            $post->images()->create([
                'path' => "news/gallery/{$number}.jpg",
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'gallery_images' => [UploadedFile::fake()->image('trzynaste.jpg')],
            ])
            ->assertSessionHasErrors([
                'gallery_images' => 'Galeria może zawierać maksymalnie 12 zdjęć.',
            ]);
    }
}
