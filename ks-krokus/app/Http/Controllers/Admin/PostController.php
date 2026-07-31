<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\PostImage;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class PostController extends Controller
{
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
        $coverPath = null;
        $galleryPaths = [];

        try {
            $coverPath = $request->hasFile('cover_image')
                ? $this->storeUploadedFile($request->file('cover_image'), 'news/covers', 'cover_image')
                : null;
            $this->storeUploadedFiles($request, $galleryPaths);

            $post = DB::transaction(function () use ($request, $coverPath, $galleryPaths): Post {
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
                );

                if ($coverPath !== null) {
                    $data['cover_image_path'] = $coverPath;
                } else {
                    $data['cover_image_alt'] = null;
                }

                $post = Post::query()->create($data);
                $this->createGalleryImages($post, $galleryPaths);

                return $post;
            });
        } catch (Throwable $exception) {
            $this->deleteFiles(array_filter([$coverPath, ...$galleryPaths]));

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
        $newCoverPath = null;
        $galleryPaths = [];

        try {
            $newCoverPath = $request->hasFile('cover_image')
                ? $this->storeUploadedFile($request->file('cover_image'), 'news/covers', 'cover_image')
                : null;
            $this->storeUploadedFiles($request, $galleryPaths);

            $pathsToDelete = DB::transaction(function () use ($request, $post, $newCoverPath, $galleryPaths): array {
                $lockedPost = Post::query()
                    ->whereKey($post->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedPost->images()->lockForUpdate()->get();
                $this->guardGalleryLimit($request, $lockedPost, count($galleryPaths));

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
                );

                $pathsToDelete = [];

                if ($newCoverPath !== null) {
                    if ($lockedPost->cover_image_path) {
                        $pathsToDelete[] = $lockedPost->cover_image_path;
                    }

                    $data['cover_image_path'] = $newCoverPath;
                } elseif ($request->boolean('remove_cover') && $lockedPost->cover_image_path) {
                    $pathsToDelete[] = $lockedPost->cover_image_path;
                    $data['cover_image_path'] = null;
                    $data['cover_image_alt'] = null;
                } elseif (! $lockedPost->cover_image_path) {
                    $data['cover_image_alt'] = null;
                }

                $lockedPost->update($data);
                $this->updateExistingImages($request, $lockedPost);
                $pathsToDelete = [
                    ...$pathsToDelete,
                    ...$this->deleteSelectedImages($request, $lockedPost),
                ];
                $this->createGalleryImages($lockedPost, $galleryPaths);

                return array_values(array_unique($pathsToDelete));
            });
        } catch (Throwable $exception) {
            $this->deleteFiles(array_filter([$newCoverPath, ...$galleryPaths]));

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
     * @param  list<string>  $paths
     */
    private function storeUploadedFiles(Request $request, array &$paths): void
    {
        $files = $request->file('gallery_images', []);

        if (! is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            $paths[] = $this->storeUploadedFile($file, 'news/gallery', 'gallery_images');
        }
    }

    /**
     * @param  list<string>  $paths
     */
    private function createGalleryImages(Post $post, array $paths): void
    {
        $nextOrder = ((int) $post->images()->max('sort_order')) + 1;

        foreach ($paths as $path) {
            $post->images()->create([
                'path' => $path,
                'alt_text' => $post->title,
                'sort_order' => $nextOrder++,
            ]);
        }
    }

    private function guardGalleryLimit(
        Request $request,
        Post $post,
        int $newImagesCount,
    ): void {
        $deleteImageIds = $request->input('delete_images', []);

        if (! is_array($deleteImageIds)) {
            $deleteImageIds = [];
        }

        $deletedImagesCount = $post->images()
            ->whereKey($deleteImageIds)
            ->count();
        $remainingImagesCount = $post->images()->count()
            - $deletedImagesCount
            + $newImagesCount;
        $maxGalleryImages = (int) config('content.gallery_max_images');

        if ($remainingImagesCount > $maxGalleryImages) {
            throw ValidationException::withMessages([
                'gallery_images' => "Galeria może zawierać maksymalnie {$maxGalleryImages} zdjęć.",
            ]);
        }
    }

    private function storeUploadedFile(
        mixed $file,
        string $directory,
        string $field,
    ): string {
        try {
            $path = $file?->store($directory, config('content.media_disk'));
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $field => 'Nie udało się zapisać zdjęcia. Spróbuj ponownie później.',
            ]);
        }

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                $field => 'Nie udało się zapisać przesłanego pliku. Spróbuj ponownie.',
            ]);
        }

        return $path;
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
            $deleted = Storage::disk(config('content.media_disk'))->delete($paths);

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

    private function updateExistingImages(
        Request $request,
        Post $post,
    ): void {
        $imageData = $request->input('existing_images', []);

        if (! is_array($imageData)) {
            return;
        }

        foreach ($imageData as $imageId => $data) {
            $image = $post->images()->find($imageId);

            if (! $image instanceof PostImage || ! is_array($data)) {
                continue;
            }

            $image->update([
                'alt_text' => $data['alt_text'] ?? null,
                'caption' => $data['caption'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function deleteSelectedImages(
        Request $request,
        Post $post,
    ): array {
        $ids = $request->input('delete_images', []);

        if (! is_array($ids) || $ids === []) {
            return [];
        }

        $images = $post->images()->whereKey($ids)->get();
        $paths = $images->pluck('path')->all();

        foreach ($images as $image) {
            $image->delete();
        }

        return $paths;
    }
}
