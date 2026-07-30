<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\SportEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $query = SportEvent::query()
            ->publiclyVisible()
            ->with('competitions')
            ->orderBy('start_at');

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->string('event_type'));
        }

        if ($request->filled('discipline')) {
            $discipline = (string) $request->string('discipline');

            $query->where(function ($builder) use ($discipline): void {
                $builder
                    ->where('discipline', $discipline)
                    ->orWhereHas(
                        'competitions',
                        fn ($competitionQuery) => $competitionQuery->where(
                            'discipline',
                            $discipline,
                        ),
                    );
            });
        }

        if ($request->filled('competition_system')) {
            $system = (string) $request->string('competition_system');

            $query->where(function ($builder) use ($system): void {
                $builder
                    ->where('competition_system', $system)
                    ->orWhereHas(
                        'competitions',
                        fn ($competitionQuery) => $competitionQuery->where(
                            'competition_system',
                            $system,
                        ),
                    );
            });
        }

        if ($request->boolean('past')) {
            $query->where('start_at', '<', now()->startOfDay())
                ->orderByDesc('start_at');
        } else {
            $query->where('start_at', '>=', now()->startOfDay());
        }

        $events = $query->paginate(12)->withQueryString();

        return view('calendar.index', [
            'events' => $events,
            'eventTypes' => EventType::options(),
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
        ]);
    }

    public function show(SportEvent $sportEvent): View
    {
        abort_unless(
            SportEvent::query()
                ->publiclyVisible()
                ->whereKey($sportEvent->getKey())
                ->exists(),
            404,
        );

        $sportEvent->load([
            'competitions',
            'eventCompetitions.competition',
        ]);

        return view('calendar.show', compact('sportEvent'));
    }
}
