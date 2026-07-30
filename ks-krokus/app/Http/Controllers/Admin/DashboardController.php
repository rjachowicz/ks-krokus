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
            $metrics = [
                'users' => User::query()->count(),
                'posts' => Post::query()->count(),
                'published_posts' => Post::query()
                    ->where('status', PublicationStatus::Published->value)
                    ->count(),
                'events' => SportEvent::query()->count(),
                'results' => EventResult::query()->count(),
            ];

            $upcomingEvents = SportEvent::query()
                ->where('start_at', '>=', now()->startOfDay())
                ->orderBy('start_at')
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

        return view('admin.dashboard', compact(
            'metrics',
            'upcomingEvents',
            'ownResults',
        ));
    }
}
