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

final class ResultsController extends Controller
{
    public function index(Request $request): View
    {
        $query = SportEvent::query()
            ->publiclyVisible()
            ->where('event_type', EventType::Competition->value)
            ->whereHas('results')
            ->withCount('results')
            ->latest('start_at');

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

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where('title', 'like', "%{$search}%");
        }

        $events = $query->paginate(12)->withQueryString();

        return view('results.index', [
            'events' => $events,
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
        ]);
    }

    public function show(SportEvent $sportEvent): View
    {
        abort_unless(
            SportEvent::query()
                ->publiclyVisible()
                ->where('event_type', EventType::Competition->value)
                ->whereKey($sportEvent->getKey())
                ->exists(),
            404,
        );

        $sportEvent->load([
            'eventCompetitions.competition',
            'eventCompetitions.results.user',
        ]);

        return view('results.show', compact('sportEvent'));
    }
}
