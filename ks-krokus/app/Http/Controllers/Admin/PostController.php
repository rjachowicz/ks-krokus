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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::query()
            ->with('author')
            ->latest();

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
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
        $post = DB::transaction(function () use ($request): Post {
            $data = $request->validated();
            $data['slug'] = UniqueSlug::for(Post::class, $data['title']);
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

            if ($request->hasFile('cover_image')) {
                $data['cover_image_path'] = $request
                    ->file('cover_image')
                    ->store('news/covers', config('content.media_disk'));
            }

            $post = Post::query()->create($data);

            $this->storeGalleryImages($request, $post);

            return $post;
        });

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
        DB::transaction(function () use ($request, $post): void {
            $data = $request->validated();
            $data['updated_by'] = $request->user()->getKey();
            $data['published_at'] = $this->resolvePublishedAt(
                $data['status'],
                $data['published_at'] ?? null,
                $post,
            );

            unset(
                $data['cover_image'],
                $data['gallery_images'],
                $data['remove_cover'],
                $data['existing_images'],
                $data['delete_images'],
            );

            $disk = Storage::disk(config('content.media_disk'));

            if ($request->boolean('remove_cover') && $post->cover_image_path) {
                $disk->delete($post->cover_image_path);
                $data['cover_image_path'] = null;
                $data['cover_image_alt'] = null;
            }

            if ($request->hasFile('cover_image')) {
                $newPath = $request
                    ->file('cover_image')
                    ->store('news/covers', config('content.media_disk'));

                if ($post->cover_image_path) {
                    $disk->delete($post->cover_image_path);
                }

                $data['cover_image_path'] = $newPath;
            }

            $post->update($data);

            $this->updateExistingImages($request, $post);
            $this->deleteSelectedImages($request, $post);
            $this->storeGalleryImages($request, $post);
        });

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Aktualność została zapisana.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

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

    private function storeGalleryImages(
        Request $request,
        Post $post,
    ): void {
        $files = $request->file('gallery_images', []);

        if (! is_array($files)) {
            return;
        }

        $nextOrder = ((int) $post->images()->max('sort_order')) + 1;

        foreach ($files as $file) {
            $path = $file->store(
                'news/gallery',
                config('content.media_disk'),
            );

            $post->images()->create([
                'path' => $path,
                'alt_text' => $post->title,
                'sort_order' => $nextOrder++,
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

    private function deleteSelectedImages(
        Request $request,
        Post $post,
    ): void {
        $ids = $request->input('delete_images', []);

        if (! is_array($ids) || $ids === []) {
            return;
        }

        $images = $post->images()->whereKey($ids)->get();
        $disk = Storage::disk(config('content.media_disk'));

        foreach ($images as $image) {
            $disk->delete($image->path);
            $image->delete();
        }
    }
}
