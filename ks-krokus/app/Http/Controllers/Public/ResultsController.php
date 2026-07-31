<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ResultsFilterRequest;
use App\Models\SportEvent;
use Illuminate\View\View;

final class ResultsController extends Controller
{
    public function index(ResultsFilterRequest $request): View
    {
        $validated = $request->validated();
        $query = SportEvent::query()
            ->publiclyVisible()
            ->where('event_type', EventType::Competition->value)
            ->whereHas('results')
            ->withCount('results')
            ->latest('start_at');

        if (filled($validated['discipline'] ?? null)) {
            $discipline = (string) $validated['discipline'];

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

        if (filled($validated['competition_system'] ?? null)) {
            $system = (string) $validated['competition_system'];

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

        if (filled($validated['q'] ?? null)) {
            $search = trim((string) $validated['q']);

            $query->where('title', 'ilike', "%{$search}%");
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
