<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\EventResult;
use App\Models\Post;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $metrics = null;

        if ($user->canManageContent()) {
            $postCounts = Post::query()
                ->selectRaw('COUNT(*) AS total')
                ->selectRaw(
                    'COUNT(*) FILTER (WHERE status = ?) AS published',
                    [PublicationStatus::Published->value],
                )
                ->toBase()
                ->first();

            $metrics = [
                'users' => $user->isAdmin() ? User::query()->count() : null,
                'posts' => (int) $postCounts->total,
                'published_posts' => (int) $postCounts->published,
                'events' => SportEvent::query()->count(),
                'results' => EventResult::query()->count(),
            ];

            $upcomingEvents = SportEvent::query()
                ->upcoming()
                ->limit(6)
                ->get();
        } else {
            $upcomingEvents = SportEvent::query()
                ->publiclyVisible()
                ->upcoming()
                ->limit(6)
                ->get();
        }

        $ownResults = EventResult::query()
            ->where('user_id', $user->getKey())
            ->with([
                'eventCompetition.event',
                'eventCompetition.competition',
            ])
            ->latest()
            ->limit(10)
            ->get();

        $notifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'metrics',
            'upcomingEvents',
            'ownResults',
            'notifications',
        ));
    }
}
