<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SportEvent;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestNews = Post::query()
            ->published()
            ->with('author')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $upcomingEvents = SportEvent::query()
            ->publiclyVisible()
            ->upcoming()
            ->limit(4)
            ->get();

        $recentResultEvents = SportEvent::query()
            ->publiclyVisible()
            ->where('event_type', EventType::Competition->value)
            ->whereHas('results')
            ->withCount('results')
            ->latest('start_at')
            ->limit(3)
            ->get();

        return view('home', compact(
            'latestNews',
            'upcomingEvents',
            'recentResultEvents',
        ));
    }
}
