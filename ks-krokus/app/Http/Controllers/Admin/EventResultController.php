<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EventType;
use App\Enums\ResultStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventResultRequest;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class EventResultController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'event_id' => ['nullable', 'integer', 'exists:sport_events,id'],
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            [],
            [
                'q' => 'wyszukiwana fraza',
                'event_id' => 'wydarzenie',
                'user_id' => 'użytkownik',
            ],
        );

        $query = EventResult::query()
            ->with([
                'eventCompetition.event',
                'eventCompetition.competition',
            ])
            ->latest();

        if (filled($filters['event_id'] ?? null)) {
            $eventId = (int) $filters['event_id'];

            $query->whereHas(
                'eventCompetition',
                fn ($builder) => $builder->where(
                    'sport_event_id',
                    $eventId,
                ),
            );
        }

        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);

            $query->where('participant_name', 'ilike', "%{$search}%");
        }

        $results = $query->paginate(30)->withQueryString();

        return view('admin.results.index', [
            'results' => $results,
            'events' => SportEvent::query()
                ->where('event_type', EventType::Competition->value)
                ->latest('start_at')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.results.create', array_merge(
            $this->formOptions(),
            [
                'selectedEventCompetitionId' => $request->integer(
                    'event_competition_id',
                ) ?: null,
            ],
        ));
    }

    public function store(
        EventResultRequest $request,
    ): RedirectResponse {
        $result = DB::transaction(function () use ($request): EventResult {
            $data = $this->normalizeData($request);

            return EventResult::query()->create([
                ...$data,
                'entered_by' => $request->user()->getKey(),
                'updated_by' => $request->user()->getKey(),
            ]);
        });

        return redirect()
            ->route('admin.results.edit', $result)
            ->with('success', 'Wynik został dodany.');
    }

    public function edit(EventResult $eventResult): View
    {
        $eventResult->load([
            'eventCompetition.event',
            'eventCompetition.competition',
        ]);

        return view('admin.results.edit', array_merge(
            $this->formOptions($eventResult),
            ['result' => $eventResult],
        ));
    }

    public function update(
        EventResultRequest $request,
        EventResult $eventResult,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $eventResult): void {
            $data = $this->normalizeData($request, $eventResult);
            $lockedResult = EventResult::query()
                ->whereKey($eventResult->getKey())
                ->lockForUpdate()
                ->first();

            if (
                $lockedResult === null
                || $lockedResult->event_competition_id
                    !== $eventResult->event_competition_id
            ) {
                throw ValidationException::withMessages([
                    'event_competition_id' => 'Wynik został w międzyczasie zmieniony. Odśwież formularz i spróbuj ponownie.',
                ]);
            }

            $data['updated_by'] = $request->user()->getKey();

            $lockedResult->update($data);
        });

        return back()->with('success', 'Wynik został zapisany.');
    }

    public function destroy(
        EventResult $eventResult,
    ): RedirectResponse {
        $eventResult->delete();

        return redirect()
            ->route('admin.results.index')
            ->with('success', 'Wynik został usunięty.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?EventResult $currentResult = null): array
    {
        $currentEventCompetitionId = $currentResult?->event_competition_id;
        $currentUserId = $currentResult?->user_id;

        $eventCompetitions = EventCompetition::query()
            ->where(function ($query) use ($currentEventCompetitionId): void {
                $query->whereHas(
                    'event',
                    fn ($eventQuery) => $eventQuery
                        ->whereNull('sport_events.deleted_at')
                        ->where(
                            'event_type',
                            EventType::Competition->value,
                        ),
                );

                if ($currentEventCompetitionId !== null) {
                    $query->orWhere(
                        'event_competitions.id',
                        $currentEventCompetitionId,
                    );
                }
            })
            ->with(['event', 'competition'])
            ->get()
            ->sortByDesc(
                fn (EventCompetition $item) => $item->event->start_at,
            );

        return [
            'eventCompetitions' => $eventCompetitions,
            'users' => User::withTrashed()
                ->where(function ($query) use ($currentUserId): void {
                    $query->whereNull('deleted_at');

                    if ($currentUserId !== null) {
                        $query->orWhere('id', $currentUserId);
                    }
                })
                ->orderBy('name')
                ->get(),
            'statuses' => ResultStatus::options(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeData(
        EventResultRequest $request,
        ?EventResult $existingResult = null,
    ): array {
        $data = $request->validated();
        $targetCompetitionId = (int) $data['event_competition_id'];
        $competitionIds = array_values(array_unique(array_filter([
            $targetCompetitionId,
            $existingResult?->event_competition_id,
        ])));
        $candidates = EventCompetition::query()
            ->whereIn('id', $competitionIds)
            ->get(['id', 'sport_event_id'])
            ->keyBy('id');
        $targetCandidate = $candidates->get($targetCompetitionId);

        if (! $targetCandidate instanceof EventCompetition) {
            throw ValidationException::withMessages([
                'event_competition_id' => 'Wybrana konkurencja nie jest już dostępna. Odśwież formularz.',
            ]);
        }

        $events = SportEvent::withTrashed()
            ->whereIn('id', $candidates->pluck('sport_event_id')->all())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $lockedCompetitions = EventCompetition::query()
            ->whereIn('id', $competitionIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $eventCompetition = $lockedCompetitions->get($targetCompetitionId);
        $event = $eventCompetition instanceof EventCompetition
            ? $events->get($eventCompetition->sport_event_id)
            : null;

        if (
            ! $eventCompetition instanceof EventCompetition
            || ! $event instanceof SportEvent
        ) {
            throw ValidationException::withMessages([
                'event_competition_id' => 'Wybrana konkurencja nie jest już dostępna. Odśwież formularz.',
            ]);
        }

        $eventCompetition->setRelation('event', $event);

        $keepsCurrentCompetition = $existingResult?->event_competition_id
            === $eventCompetition->getKey();

        if (
            $eventCompetition->event->event_type !== EventType::Competition
            || ($eventCompetition->event->trashed() && ! $keepsCurrentCompetition)
        ) {
            throw ValidationException::withMessages([
                'event_competition_id' => 'Wybierz konkurencję należącą do istniejących zawodów.',
            ]);
        }

        if (! empty($data['user_id'])) {
            $user = User::withTrashed()
                ->whereKey($data['user_id'])
                ->lockForUpdate()
                ->first();
            $sameLinkedUser = $user !== null
                && $existingResult?->user_id === $user->getKey();

            if ($user === null || ($user->trashed() && ! $sameLinkedUser)) {
                throw ValidationException::withMessages([
                    'user_id' => 'Wybrany użytkownik nie istnieje lub został usunięty.',
                ]);
            }

            $data['participant_name'] = $sameLinkedUser
                && filled($existingResult?->participant_name)
                    ? $existingResult->participant_name
                    : $user->name;
        } else {
            $data['user_id'] = null;
        }

        return $data;
    }
}
