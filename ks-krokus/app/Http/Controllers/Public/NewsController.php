<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::query()
            ->published()
            ->with('author')
            ->latest('published_at');

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        return view('news.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        abort_unless(
            Post::query()->published()->whereKey($post->getKey())->exists(),
            404,
        );

        $post->load(['author', 'images']);

        $morePosts = Post::query()
            ->published()
            ->where($post->getKeyName(), '!=', $post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('news.show', compact('post', 'morePosts'));
    }
}
