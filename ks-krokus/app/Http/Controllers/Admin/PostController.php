<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\PostImage;
use App\Support\MediaCrop;
use App\Support\MediaFileCleanup;
use App\Support\MediaImageStorage;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class PostController extends Controller
{
    public function __construct(
        private readonly MediaImageStorage $imageStorage,
        private readonly MediaFileCleanup $fileCleanup,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', Rule::enum(PublicationStatus::class)],
            ],
            [],
            ['q' => 'wyszukiwana fraza', 'status' => 'status publikacji'],
        );

        $query = Post::query()
            ->with('author')
            ->latest();

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'ilike', "%{$search}%")
                    ->orWhere('excerpt', 'ilike', "%{$search}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $posts = $query->paginate(20)->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'statuses' => PublicationStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'statuses' => PublicationStatus::options(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $cover = null;
        $gallery = [];

        try {
            $cover = $request->hasFile('cover_image')
                ? $this->storeUploadedFile(
                    $request->file('cover_image'),
                    'news/covers',
                    'news/variants/covers',
                    (int) config('media.cover_width'),
                    (int) config('media.cover_height'),
                    MediaCrop::fromInput($request->input('cover_crop')),
                    'cover_image',
                )
                : null;
            $this->storeUploadedFiles($request, $gallery);

            $post = DB::transaction(function () use ($request, $cover, $gallery): Post {
                $data = $request->validated();
                $data['slug'] = UniqueSlug::for(Post::class, $data['title']);
                $data['content_format'] = $data['content_format'] ?? 'plain';
                $data['author_id'] = $request->user()->getKey();
                $data['updated_by'] = $request->user()->getKey();
                $data['published_at'] = $this->resolvePublishedAt(
                    $data['status'],
                    $data['published_at'] ?? null,
                );

                unset(
                    $data['cover_image'],
                    $data['gallery_images'],
                    $data['remove_cover'],
                    $data['existing_images'],
                    $data['delete_images'],
                    $data['cover_crop'],
                    $data['gallery_crops'],
                );

                if ($cover !== null) {
                    $data['cover_image_path'] = $cover['path'];
                    $data['cover_variant_path'] = $cover['variant_path'];
                    $data['cover_crop'] = $cover['crop'];
                } else {
                    $data['cover_image_alt'] = null;
                }

                $post = Post::query()->create($data);
                $this->createGalleryImages($post, $gallery);

                return $post;
            });
        } catch (Throwable $exception) {
            $this->deleteFiles([
                ...$this->storedPaths($cover === null ? [] : [$cover]),
                ...$this->storedPaths($gallery),
            ]);

            throw $exception;
        }

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Aktualność została utworzona.');
    }

    public function edit(Post $post): View
    {
        $post->load('images');

        return view('admin.posts.edit', [
            'post' => $post,
            'statuses' => PublicationStatus::options(),
        ]);
    }

    public function update(
        PostRequest $request,
        Post $post,
    ): RedirectResponse {
        $newCover = null;
        $gallery = [];
        $generatedVariants = [];

        try {
            $newCover = $request->hasFile('cover_image')
                ? $this->storeUploadedFile(
                    $request->file('cover_image'),
                    'news/covers',
                    'news/variants/covers',
                    (int) config('media.cover_width'),
                    (int) config('media.cover_height'),
                    MediaCrop::fromInput($request->input('cover_crop')),
                    'cover_image',
                )
                : null;
            $this->storeUploadedFiles($request, $gallery);

            $pathsToDelete = DB::transaction(function () use ($request, $post, $newCover, $gallery, &$generatedVariants): array {
                $lockedPost = Post::query()
                    ->whereKey($post->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedImages = $lockedPost->images()
                    ->lockForUpdate()
                    ->get();
                $this->guardGalleryLimit(
                    $request,
                    $lockedImages,
                    count($gallery),
                );

                $data = $request->validated();
                $data['updated_by'] = $request->user()->getKey();
                $data['content_format'] = $data['content_format'] ?? $lockedPost->content_format;
                $data['published_at'] = $this->resolvePublishedAt(
                    $data['status'],
                    $data['published_at'] ?? null,
                    $lockedPost,
                );

                unset(
                    $data['cover_image'],
                    $data['gallery_images'],
                    $data['remove_cover'],
                    $data['existing_images'],
                    $data['delete_images'],
                    $data['cover_crop'],
                    $data['gallery_crops'],
                );

                $pathsToDelete = [];

                if ($newCover !== null) {
                    if ($lockedPost->cover_image_path) {
                        $pathsToDelete[] = $lockedPost->cover_image_path;
                    }

                    if ($lockedPost->cover_variant_path) {
                        $pathsToDelete[] = $lockedPost->cover_variant_path;
                    }

                    $data['cover_image_path'] = $newCover['path'];
                    $data['cover_variant_path'] = $newCover['variant_path'];
                    $data['cover_crop'] = $newCover['crop'];
                } elseif ($request->boolean('remove_cover') && $lockedPost->cover_image_path) {
                    $pathsToDelete[] = $lockedPost->cover_image_path;
                    if ($lockedPost->cover_variant_path) {
                        $pathsToDelete[] = $lockedPost->cover_variant_path;
                    }
                    $data['cover_image_path'] = null;
                    $data['cover_variant_path'] = null;
                    $data['cover_crop'] = null;
                    $data['cover_image_alt'] = null;
                } elseif (
                    $lockedPost->cover_image_path
                    && ($coverCrop = MediaCrop::fromInput($request->input('cover_crop'))) !== null
                    && $coverCrop !== $lockedPost->cover_crop
                ) {
                    $variant = $this->imageStorage->regenerateVariant(
                        $lockedPost->cover_image_path,
                        'news/variants/covers',
                        (int) config('media.cover_width'),
                        (int) config('media.cover_height'),
                        $coverCrop,
                        'cover_crop',
                    );
                    $generatedVariants[] = $variant['path'];
                    if ($lockedPost->cover_variant_path) {
                        $pathsToDelete[] = $lockedPost->cover_variant_path;
                    }
                    $data['cover_variant_path'] = $variant['path'];
                    $data['cover_crop'] = $variant['crop'];
                } elseif (! $lockedPost->cover_image_path) {
                    $data['cover_image_alt'] = null;
                }

                $lockedPost->update($data);
                $pathsToDelete = [
                    ...$pathsToDelete,
                    ...$this->updateExistingImages($request, $lockedImages, $generatedVariants),
                ];
                $pathsToDelete = [
                    ...$pathsToDelete,
                    ...$this->deleteSelectedImages(
                        $request,
                        $lockedPost,
                        $lockedImages,
                    ),
                ];
                $this->createGalleryImages($lockedPost, $gallery);

                return array_values(array_unique($pathsToDelete));
            });
        } catch (Throwable $exception) {
            $this->deleteFiles([
                ...$this->storedPaths($newCover === null ? [] : [$newCover]),
                ...$this->storedPaths($gallery),
                ...$generatedVariants,
            ]);

            throw $exception;
        }

        $this->deleteFiles($pathsToDelete);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Aktualność została zapisana.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        DB::transaction(function () use ($post): void {
            $lockedPost = Post::query()
                ->whereKey($post->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedPost->delete();
        });

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Aktualność została przeniesiona do kosza.');
    }

    private function resolvePublishedAt(
        string $status,
        mixed $publishedAt,
        ?Post $post = null,
    ): mixed {
        if ($status !== PublicationStatus::Published->value) {
            return $publishedAt ?: null;
        }

        return $publishedAt
            ?: $post?->published_at
            ?: now();
    }

    /**
     * @param  list<array{path: string, variant_path: string, crop: array{x: float, y: float, width: float, height: float}}>  $stored
     */
    private function storeUploadedFiles(Request $request, array &$stored): void
    {
        $files = $request->file('gallery_images', []);
        $crops = (array) $request->input('gallery_crops', []);

        if (! is_array($files)) {
            return;
        }

        foreach (array_values($files) as $index => $file) {
            $stored[] = $this->storeUploadedFile(
                $file,
                'news/gallery',
                'news/variants/thumbnails',
                (int) config('media.thumbnail_width'),
                (int) config('media.thumbnail_height'),
                MediaCrop::fromInput($crops[$index] ?? null),
                'gallery_images',
            );
        }
    }

    /**
     * @param  list<array{path: string, variant_path: string, crop: array{x: float, y: float, width: float, height: float}}>  $stored
     */
    private function createGalleryImages(Post $post, array $stored): void
    {
        $nextOrder = ((int) $post->images()->max('sort_order')) + 1;

        foreach ($stored as $image) {
            $post->images()->create([
                'path' => $image['path'],
                'thumbnail_path' => $image['variant_path'],
                'crop' => $image['crop'],
                'alt_text' => $post->title,
                'sort_order' => $nextOrder++,
            ]);
        }
    }

    private function guardGalleryLimit(
        Request $request,
        EloquentCollection $images,
        int $newImagesCount,
    ): void {
        $deleteImageIds = $this->submittedImageIds(
            $request,
            'delete_images',
        );
        $deletedImagesCount = $images->whereIn('id', $deleteImageIds)->count();
        $remainingImagesCount = $images->count()
            - $deletedImagesCount
            + $newImagesCount;
        $maxGalleryImages = (int) config('content.gallery_max_images');

        if ($remainingImagesCount > $maxGalleryImages) {
            throw ValidationException::withMessages([
                'gallery_images' => "Galeria może zawierać maksymalnie {$maxGalleryImages} zdjęć.",
            ]);
        }
    }

    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{path: string, variant_path: string, crop: array{x: float, y: float, width: float, height: float}}
     */
    private function storeUploadedFile(
        mixed $file,
        string $directory,
        string $variantDirectory,
        int $variantWidth,
        int $variantHeight,
        ?array $crop,
        string $field,
    ): array {
        return $this->imageStorage->store(
            $file,
            $directory,
            $variantDirectory,
            $variantWidth,
            $variantHeight,
            $crop,
            $field,
        );
    }

    /**
     * @param  array<array-key, string|null>  $paths
     */
    private function deleteFiles(array $paths): void
    {
        $paths = array_values(array_filter($paths));

        if ($paths === []) {
            return;
        }

        try {
            $deleted = $this->fileCleanup->deleteUnreferenced($paths);

            if (! $deleted) {
                Log::warning('Nie wszystkie nieużywane pliki aktualności zostały usunięte.', [
                    'paths' => $paths,
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Nie udało się usunąć nieużywanych plików aktualności.', [
                'paths' => $paths,
            ]);
        }
    }

    /**
     * @param  list<string>  $generatedVariants
     * @return list<string>
     */
    private function updateExistingImages(
        Request $request,
        EloquentCollection $images,
        array &$generatedVariants,
    ): array {
        $imageData = $request->input('existing_images', []);

        if (! is_array($imageData)) {
            return [];
        }

        $imagesById = $images->keyBy('id');
        $pathsToDelete = [];

        foreach ($imageData as $imageId => $data) {
            $image = $imagesById->get((int) $imageId);

            if (! $image instanceof PostImage || ! is_array($data)) {
                continue;
            }

            $update = [
                'alt_text' => $data['alt_text'] ?? null,
                'caption' => $data['caption'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ];
            $crop = MediaCrop::fromInput($data['crop'] ?? null);

            if ($crop !== null && $crop !== $image->crop) {
                $variant = $this->imageStorage->regenerateVariant(
                    $image->path,
                    'news/variants/thumbnails',
                    (int) config('media.thumbnail_width'),
                    (int) config('media.thumbnail_height'),
                    $crop,
                    "existing_images.{$image->getKey()}.crop",
                );
                $generatedVariants[] = $variant['path'];

                if ($image->thumbnail_path) {
                    $pathsToDelete[] = $image->thumbnail_path;
                }

                $update['thumbnail_path'] = $variant['path'];
                $update['crop'] = $variant['crop'];
            }

            $image->update($update);
        }

        return $pathsToDelete;
    }

    /**
     * @return list<string>
     */
    private function deleteSelectedImages(
        Request $request,
        Post $post,
        EloquentCollection $images,
    ): array {
        $ids = $this->submittedImageIds($request, 'delete_images');

        if ($ids === []) {
            return [];
        }

        $selectedImages = $images->whereIn('id', $ids);

        if ($selectedImages->isEmpty()) {
            return [];
        }

        $paths = $selectedImages
            ->flatMap(static fn (PostImage $image): array => $image->filePaths())
            ->values()
            ->all();
        $post->images()->whereKey($selectedImages->modelKeys())->delete();

        return $paths;
    }

    /**
     * @return list<int>
     */
    private function submittedImageIds(
        Request $request,
        string $field,
    ): array {
        $values = $request->input($field, []);

        if (! is_array($values)) {
            return [];
        }

        $ids = [];

        foreach ($values as $value) {
            if (! is_int($value) && ! is_string($value)) {
                continue;
            }

            $id = filter_var($value, FILTER_VALIDATE_INT);

            if ($id !== false && $id > 0) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  list<array{path: string, variant_path: string, crop: array{x: float, y: float, width: float, height: float}}>  $stored
     * @return list<string>
     */
    private function storedPaths(array $stored): array
    {
        return array_merge(...array_map(
            static fn (array $image): array => [$image['path'], $image['variant_path']],
            $stored,
        )) ?: [];
    }
}
