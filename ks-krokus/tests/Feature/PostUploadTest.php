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
use Illuminate\Support\Str;
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

    public function test_new_cover_takes_precedence_over_remove_checkbox_and_keeps_new_alt_text(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Wymiana okładki',
            'slug' => 'wymiana-okladki',
            'content' => 'Treść',
            'cover_image_path' => 'news/covers/stara.jpg',
            'cover_image_alt' => 'Stary opis',
            'status' => PublicationStatus::Draft,
        ]);
        Storage::disk('public')->put($post->cover_image_path, 'stare zdjęcie');

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'remove_cover' => '1',
                'cover_image_alt' => 'Nowy opis alternatywny',
                'cover_image' => UploadedFile::fake()->image('nowa.png'),
            ])
            ->assertSessionHasNoErrors();

        $post->refresh();

        self::assertNotSame('news/covers/stara.jpg', $post->cover_image_path);
        self::assertSame('Nowy opis alternatywny', $post->cover_image_alt);
        Storage::disk('public')->assertMissing('news/covers/stara.jpg');
        Storage::disk('public')->assertExists($post->cover_image_path);
    }

    public function test_cover_can_be_removed_with_its_file_and_orphan_alt_is_cleared(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Usuwanie okładki',
            'slug' => 'usuwanie-okladki',
            'content' => 'Treść',
            'cover_image_path' => 'news/covers/do-usuniecia.jpg',
            'cover_image_alt' => 'Opis do usunięcia',
            'status' => PublicationStatus::Draft,
        ]);
        Storage::disk('public')->put($post->cover_image_path, 'zdjęcie');

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'remove_cover' => '1',
                'cover_image_alt' => 'Nie powinien zostać',
            ])
            ->assertSessionHasNoErrors();

        $post->refresh();

        self::assertNull($post->cover_image_path);
        self::assertNull($post->cover_image_alt);
        Storage::disk('public')->assertMissing('news/covers/do-usuniecia.jpg');
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

    public function test_gallery_images_from_another_post_cannot_be_edited_or_removed(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $editedPost = Post::query()->create([
            'title' => 'Edytowana aktualność',
            'slug' => 'edytowana-aktualnosc',
            'content' => 'Treść',
            'status' => PublicationStatus::Draft,
        ]);
        $otherPost = Post::query()->create([
            'title' => 'Inna aktualność',
            'slug' => 'inna-aktualnosc',
            'content' => 'Treść',
            'status' => PublicationStatus::Draft,
        ]);
        $otherImage = $otherPost->images()->create([
            'path' => 'news/gallery/cudze.jpg',
            'alt_text' => 'Oryginalny opis',
            'sort_order' => 1,
        ]);
        Storage::disk('public')->put($otherImage->path, 'cudze zdjęcie');

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $editedPost), [
                'title' => $editedPost->title,
                'content' => $editedPost->content,
                'status' => PublicationStatus::Draft->value,
                'existing_images' => [
                    $otherImage->id => [
                        'alt_text' => 'Podmieniony opis',
                        'sort_order' => 2,
                    ],
                ],
                'delete_images' => [$otherImage->id],
            ])
            ->assertSessionHasErrors([
                'existing_images',
                'delete_images.0',
            ]);

        self::assertSame('Oryginalny opis', $otherImage->fresh()->alt_text);
        Storage::disk('public')->assertExists($otherImage->path);
    }

    public function test_malformed_gallery_metadata_returns_an_error_without_breaking_edit_view(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Metadane galerii',
            'slug' => 'metadane-galerii',
            'content' => 'Treść',
            'status' => PublicationStatus::Draft,
        ]);
        $image = $post->images()->create([
            'path' => 'news/gallery/metadane.jpg',
            'alt_text' => 'Poprawny opis',
            'sort_order' => 1,
        ]);

        $this->actingAs($moderator)
            ->from(route('admin.posts.edit', $post))
            ->followingRedirects()
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'existing_images' => [
                    $image->id => [
                        'alt_text' => ['nieprawidłowa wartość'],
                        'sort_order' => 1,
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee("post-image-{$image->id}-alt-error", false)
            ->assertDontSee('validation.', false);

        self::assertSame('Poprawny opis', $image->fresh()->alt_text);
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

    public function test_php_upload_limit_error_is_polish_and_keeps_existing_cover(): void
    {
        Storage::fake('public');
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Aktualność z okładką',
            'slug' => 'aktualnosc-z-okladka',
            'content' => 'Treść',
            'cover_image_path' => 'news/covers/stara.jpg',
            'status' => PublicationStatus::Draft,
        ]);
        Storage::disk('public')->put($post->cover_image_path, 'stare zdjęcie');

        $failedUpload = new UploadedFile(
            __FILE__,
            'za-duze.jpg',
            'image/jpeg',
            UPLOAD_ERR_INI_SIZE,
            true,
        );

        $this->actingAs($moderator)
            ->put(route('admin.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PublicationStatus::Draft->value,
                'cover_image' => $failedUpload,
            ])
            ->assertSessionHasErrors([
                'cover_image' => 'Nie udało się przesłać zdjęcia głównego. Pojedynczy plik może mieć maksymalnie 6 MB.',
            ]);

        self::assertSame('news/covers/stara.jpg', $post->fresh()->cover_image_path);
        Storage::disk('public')->assertExists('news/covers/stara.jpg');
    }

    public function test_server_limits_leave_room_for_laravel_validation(): void
    {
        $cgiConfiguration = file_get_contents(public_path('.user.ini'));
        $railpackConfiguration = file_get_contents(base_path('php.ini'));
        $composerConfiguration = file_get_contents(base_path('composer.json'));

        self::assertIsString($cgiConfiguration);
        self::assertIsString($railpackConfiguration);
        self::assertIsString($composerConfiguration);

        foreach ([$cgiConfiguration, $railpackConfiguration] as $configuration) {
            self::assertStringContainsString('upload_max_filesize = 8M', $configuration);
            self::assertStringContainsString('post_max_size = 85M', $configuration);
            self::assertStringContainsString('max_file_uploads = 20', $configuration);
        }

        self::assertStringContainsString(
            'php -d upload_max_filesize=8M -d post_max_size=85M -d max_file_uploads=20 -S',
            $composerConfiguration,
        );
        self::assertStringContainsString('-t public server.php', $composerConfiguration);
        self::assertFileExists(base_path('server.php'));
        self::assertStringNotContainsString('upload_max_filesize=8M -d post_max_size=85M artisan serve', $composerConfiguration);
        self::assertSame(6144, config('content.image_max_size_kb'));
        self::assertSame(12, config('content.gallery_max_images'));
    }

    public function test_configured_media_disk_is_writable_and_publicly_available(): void
    {
        $disk = Storage::disk(config('content.media_disk'));
        $path = 'healthchecks/'.Str::uuid().'.txt';

        try {
            self::assertTrue($disk->put($path, 'KS Krokus'));
            self::assertTrue($disk->exists($path));
            self::assertStringContainsString('/storage/healthchecks/', $disk->url($path));
            self::assertTrue(is_dir(public_path('storage')));
        } finally {
            $disk->delete($path);
        }

        self::assertFalse($disk->exists($path));
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
