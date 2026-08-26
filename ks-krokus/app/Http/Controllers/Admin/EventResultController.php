<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EventType;
use App\Enums\IpscDivision;
use App\Enums\MemberAgeCategory;
use App\Enums\PublicationStatus;
use App\Enums\ResultStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventResultFilterRequest;
use App\Http\Requests\Admin\EventResultParticipantSearchRequest;
use App\Http\Requests\Admin\EventResultRequest;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class EventResultController extends Controller
{
    public function index(EventResultFilterRequest $request): View
    {
        $filters = $request->validated();

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

        if (filled($filters['event_competition_id'] ?? null)) {
            $query->where(
                'event_competition_id',
                (int) $filters['event_competition_id'],
            );
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', (string) $filters['status']);
        }

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('participant_name', 'ilike', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn ($userQuery) => $userQuery->where(
                            'name',
                            'ilike',
                            "%{$search}%",
                        ),
                    );
            });
        }

        $results = $query->paginate(30)->withQueryString();

        return view('admin.results.index', [
            'results' => $results,
            'events' => SportEvent::withTrashed()
                ->where('event_type', EventType::Competition->value)
                ->whereHas('results')
                ->latest('start_at')
                ->get(),
            'eventCompetitions' => EventCompetition::query()
                ->whereHas('results')
                ->with(['event', 'competition'])
                ->get()
                ->sortByDesc(fn (EventCompetition $item) => $item->event->start_at),
            'statuses' => ResultStatus::options(),
        ]);
    }

    public function participants(
        EventResultParticipantSearchRequest $request,
    ): JsonResponse {
        $search = trim((string) $request->validated('q'));

        $users = User::query()
            ->select(['id', 'name'])
            ->with(['memberProfile:id,user_id,age_category'])
            ->where('is_active', true)
            ->where('name', 'ilike', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(static fn (User $user): array => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'club_name' => (string) config('club.short_name'),
                'category' => $user->memberProfile?->age_category?->value,
                'category_label' => $user->memberProfile?->age_category?->label(),
            ]);

        return response()->json(['data' => $users]);
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
        Gate::authorize('delete', $eventResult);

        DB::transaction(function () use ($eventResult): void {
            $eventId = $eventResult->eventCompetition()
                ->value('sport_event_id');
            $event = SportEvent::withTrashed()
                ->whereKey($eventId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($event->status === PublicationStatus::Archived) {
                throw new AuthorizationException(
                    'Wyniki archiwalnych zawodów są tylko do odczytu.',
                );
            }

            EventResult::query()
                ->whereKey($eventResult->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });

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
                        )
                        ->where(
                            'status',
                            PublicationStatus::Published->value,
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
            'currentUser' => $currentUserId === null
                ? null
                : User::withTrashed()->find($currentUserId),
            'ageCategories' => MemberAgeCategory::options(),
            'ipscDivisions' => IpscDivision::options(),
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

        $sourceEvent = $existingResult === null
            ? null
            : $candidates->get($existingResult->event_competition_id)?->sport_event_id;
        $lockedSourceEvent = $sourceEvent === null ? null : $events->get($sourceEvent);

        if (
            $lockedSourceEvent instanceof SportEvent
            && $lockedSourceEvent->status === PublicationStatus::Archived
        ) {
            throw ValidationException::withMessages([
                'event_competition_id' => 'Wyniki archiwalnych zawodów są tylko do odczytu.',
            ]);
        }

        if (
            $eventCompetition->event->event_type !== EventType::Competition
            || $eventCompetition->event->status === PublicationStatus::Archived
            || (
                (
                    $eventCompetition->event->trashed()
                    || $eventCompetition->event->status !== PublicationStatus::Published
                )
                && ! $keepsCurrentCompetition
            )
        ) {
            throw ValidationException::withMessages([
                'event_competition_id' => 'Wybierz konkurencję należącą do opublikowanych zawodów.',
            ]);
        }

        if (! empty($data['user_id'])) {
            $user = User::withTrashed()
                ->with('memberProfile')
                ->whereKey($data['user_id'])
                ->lockForUpdate()
                ->first();
            $sameLinkedUser = $user !== null
                && $existingResult?->user_id === $user->getKey();

            if (
                $user === null
                || (($user->trashed() || ! $user->is_active) && ! $sameLinkedUser)
            ) {
                throw ValidationException::withMessages([
                    'user_id' => 'Wybrany użytkownik nie istnieje albo nie ma aktywnego konta.',
                ]);
            }

            if ($sameLinkedUser) {
                $data['participant_name'] = $existingResult->participant_name;
                $data['club_name'] = $existingResult->club_name;
                $data['category'] = $existingResult->category;
            } else {
                $data['participant_name'] = $user->name;
                $data['club_name'] = (string) config('club.short_name');
                $data['category'] = $user->memberProfile?->age_category?->value;
            }
        } else {
            $data['user_id'] = null;
        }

        return $data;
    }
}
