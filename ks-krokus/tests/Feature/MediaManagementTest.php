<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\SaleListingCategory;
use App\Enums\SaleListingFirearmType;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\SaleListing;
use App\Models\SaleListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MediaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_modules_use_the_configured_media_disk(): void
    {
        config()->set('media.disk', 'media-testing');
        config()->set('content.media_disk', 'media-testing');
        config()->set('listings.media_disk', 'media-testing');
        Storage::fake('media-testing');
        Storage::fake('public');
        $moderator = User::factory()->create(['role' => UserRole::Moderator, 'is_active' => true]);
        $owner = User::factory()->create(['is_active' => true]);

        $this->actingAs($moderator)->post(route('admin.posts.store'), [
            'title' => 'Wspólny dysk mediów',
            'content' => 'Treść aktualności.',
            'status' => PublicationStatus::Draft->value,
            'cover_image' => UploadedFile::fake()->image('okladka.jpg', 1200, 800),
        ])->assertSessionHasNoErrors();

        $this->actingAs($owner)->post(route('admin.my-listings.store'), [
            ...$this->listingData(),
            'images' => [UploadedFile::fake()->image('ogloszenie.jpg', 1200, 800)],
            'intent' => 'draft',
        ])->assertSessionHasNoErrors();

        $post = Post::query()->firstOrFail();
        $listingImage = SaleListingImage::query()->firstOrFail();
        Storage::disk('media-testing')->assertExists([
            $post->cover_image_path,
            $post->cover_variant_path,
            $listingImage->path,
            $listingImage->thumbnail_path,
        ]);
        self::assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_missing_files_render_placeholders_instead_of_broken_urls(): void
    {
        Storage::fake((string) config('media.disk'));
        $post = Post::query()->create([
            'title' => 'Brakująca okładka',
            'slug' => 'brakujaca-okladka',
            'content' => 'Treść.',
            'status' => PublicationStatus::Published,
            'published_at' => now()->subMinute(),
            'cover_image_path' => 'news/covers/brak.jpg',
            'cover_variant_path' => 'news/variants/covers/brak.jpg',
        ]);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSeeText('Zdjęcie aktualności jest chwilowo niedostępne')
            ->assertDontSee('/storage/news/covers/brak.jpg', false);

        $listing = SaleListing::factory()->approved()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create([
            'path' => 'sale-listings/images/brak.jpg',
            'thumbnail_path' => 'sale-listings/thumbnails/brak.jpg',
        ]);

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSeeText('Brak zdjęć ogłoszenia')
            ->assertDontSee('/storage/sale-listings/images/brak.jpg', false);
    }

    public function test_media_audit_reports_missing_files_and_never_deletes_by_default(): void
    {
        Storage::fake((string) config('media.disk'));
        $post = Post::query()->create([
            'title' => 'Audyt mediów',
            'slug' => 'audyt-mediow',
            'content' => 'Treść.',
            'status' => PublicationStatus::Draft,
            'cover_image_path' => 'news/covers/istnieje.jpg',
            'cover_variant_path' => 'news/variants/covers/brak.jpg',
        ]);
        Storage::disk((string) config('media.disk'))->put($post->cover_image_path, 'kontrolna zawartość');
        $post->images()->create([
            'path' => 'news/gallery/brak.jpg',
            'thumbnail_path' => 'news/variants/thumbnails/brak.jpg',
        ]);
        $listing = SaleListing::factory()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create([
            'path' => 'sale-listings/images/brak.jpg',
            'thumbnail_path' => 'sale-listings/thumbnails/brak.jpg',
        ]);

        $this->artisan('media:audit')
            ->expectsOutputToContain('Łącznie brakujących plików: 5')
            ->expectsOutputToContain('Żaden rekord ani plik nie został usunięty.')
            ->assertExitCode(1);

        Storage::disk((string) config('media.disk'))->assertExists($post->cover_image_path);
        self::assertDatabaseHas('posts', ['id' => $post->id]);
        self::assertDatabaseHas('sale_listing_images', ['sale_listing_id' => $listing->id]);
    }

    public function test_thumbnail_is_generated_while_full_image_is_preserved(): void
    {
        Storage::fake((string) config('media.disk'));
        $owner = User::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('admin.my-listings.store'), [
            ...$this->listingData(),
            'images' => [UploadedFile::fake()->image('pelny.jpg', 1200, 800)],
            'new_image_crop' => [[
                'x' => 0.1,
                'y' => 0.0,
                'width' => 0.8,
                'height' => 0.9,
            ]],
            'intent' => 'draft',
        ])->assertSessionHasNoErrors();

        $image = SaleListingImage::query()->firstOrFail();
        self::assertNotSame($image->path, $image->thumbnail_path);
        Storage::disk((string) config('media.disk'))->assertExists([$image->path, $image->thumbnail_path]);
        self::assertSame([1200, 800], $this->dimensions($image->path));
        self::assertSame([480, 360], $this->dimensions($image->thumbnail_path));
    }

    public function test_existing_image_can_be_recropped_and_old_shared_variant_is_not_deleted(): void
    {
        Storage::fake((string) config('media.disk'));
        $owner = User::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('admin.my-listings.store'), [
            ...$this->listingData(),
            'images' => [UploadedFile::fake()->image('pelny.jpg', 1200, 800)],
            'intent' => 'draft',
        ])->assertSessionHasNoErrors();

        $listing = SaleListing::query()->firstOrFail();
        $image = $listing->images()->firstOrFail();
        $originalPath = $image->path;
        $oldThumbnailPath = $image->thumbnail_path;
        $shared = $listing->images()->create([
            'path' => $image->path,
            'thumbnail_path' => $oldThumbnailPath,
            'crop' => $image->crop,
            'sort_order' => 1,
            'is_primary' => false,
        ]);

        $this->actingAs($owner)->put(route('admin.my-listings.update', $listing), [
            ...$this->listingData(),
            'intent' => 'save',
            'existing_images' => [
                $image->id => [
                    'alt_text' => 'Nowy kadr',
                    'sort_order' => 0,
                    'crop' => ['x' => 0.1, 'y' => 0.0, 'width' => 0.8, 'height' => 0.9],
                ],
                $shared->id => [
                    'alt_text' => 'Współdzielony wariant',
                    'sort_order' => 1,
                ],
            ],
        ])->assertSessionHasNoErrors();

        $image->refresh();
        self::assertSame($originalPath, $image->path);
        self::assertNotSame($oldThumbnailPath, $image->thumbnail_path);
        Storage::disk((string) config('media.disk'))->assertExists([
            $originalPath,
            $image->thumbnail_path,
            $oldThumbnailPath,
        ]);
    }

    public function test_invalid_crop_coordinates_are_rejected_without_writing_files(): void
    {
        Storage::fake((string) config('media.disk'));
        $moderator = User::factory()->create(['role' => UserRole::Moderator, 'is_active' => true]);

        $this->actingAs($moderator)->post(route('admin.posts.store'), [
            'title' => 'Błędny kadr',
            'content' => 'Treść aktualności.',
            'status' => PublicationStatus::Draft->value,
            'cover_image' => UploadedFile::fake()->image('okladka.jpg', 1200, 800),
            'cover_crop' => ['x' => -0.1, 'y' => 0, 'width' => 1.2, 'height' => 1],
        ])->assertSessionHasErrors(['cover_crop.x', 'cover_crop.width']);

        self::assertDatabaseCount('posts', 0);
        self::assertSame([], Storage::disk((string) config('media.disk'))->allFiles());
    }

    public function test_storage_link_command_and_railway_startup_are_idempotent(): void
    {
        $suffix = Str::uuid()->toString();
        $target = storage_path("framework/testing/media-link-target-{$suffix}");
        $link = storage_path("framework/testing/media-link-{$suffix}");
        mkdir($target, 0777, true);
        config()->set('filesystems.links', [$link => $target]);

        try {
            $this->artisan('storage:link')->assertExitCode(0);
            $this->artisan('storage:link')->assertExitCode(0);
            self::assertTrue(is_link($link) || is_dir($link));
        } finally {
            if (is_link($link) || is_dir($link)) {
                rmdir($link);
            }
            if (is_dir($target)) {
                rmdir($target);
            }
        }

        $startup = (string) file_get_contents(base_path('railway/init-app.sh'));
        self::assertStringContainsString('if ! php artisan storage:link; then', $startup);
        self::assertStringContainsString('[ ! -L public/storage ]', $startup);
    }

    /** @return array<string, mixed> */
    private function listingData(): array
    {
        return [
            'title' => 'Karabinek sportowy z optyką',
            'category' => SaleListingCategory::LongGun->value,
            'firearm_type' => SaleListingFirearmType::Carbine->value,
            'description' => 'Karabinek w bardzo dobrym stanie, regularnie czyszczony i gotowy do treningu.',
            'contact_name' => 'Jan',
            'show_phone' => '0',
            'show_email' => '0',
        ];
    }

    /** @return array{0: int, 1: int} */
    private function dimensions(string $path): array
    {
        $size = getimagesizefromstring(Storage::disk((string) config('media.disk'))->get($path));
        self::assertIsArray($size);

        return [(int) $size[0], (int) $size[1]];
    }
}
