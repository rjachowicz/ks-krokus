<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ResultsFilterRequest;
use App\Models\SportEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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
            $query->matchingDiscipline((string) $validated['discipline']);
        }

        if (filled($validated['competition_system'] ?? null)) {
            $query->matchingCompetitionSystem(
                (string) $validated['competition_system'],
            );
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

    public function show(ResultsFilterRequest $request, string $slug): View
    {
        $validated = $request->validated();
        $search = filled($validated['q'] ?? null)
            ? trim((string) $validated['q'])
            : null;

        $sportEvent = SportEvent::query()
            ->publiclyVisible()
            ->where('event_type', EventType::Competition->value)
            ->with([
                'eventCompetitions' => function (Builder|Relation $query) use ($search): void {
                    if ($search !== null) {
                        $query->whereHas(
                            'results',
                            fn (Builder $resultQuery) => $this->applyParticipantSearch(
                                $resultQuery,
                                $search,
                            ),
                        );
                    }
                },
                'eventCompetitions.competition',
                'eventCompetitions.results' => function (Builder|Relation $query) use ($search): void {
                    if ($search !== null) {
                        $this->applyParticipantSearch($query, $search);
                    }
                },
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('results.show', [
            'sportEvent' => $sportEvent,
            'search' => $search,
            'matchedResultsCount' => $sportEvent->eventCompetitions
                ->sum(fn ($eventCompetition): int => $eventCompetition->results->count()),
        ]);
    }

    private function applyParticipantSearch(
        Builder|Relation $query,
        string $search,
    ): void {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('participant_name', 'ilike', "%{$search}%")
                ->orWhereHas(
                    'user',
                    fn (Builder $userQuery) => $userQuery->where(
                        'name',
                        'ilike',
                        "%{$search}%",
                    ),
                );
        });
    }
}
