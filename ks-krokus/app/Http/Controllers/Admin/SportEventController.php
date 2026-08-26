<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SportEventRequest;
use App\Models\CompetitionDefinition;
use App\Models\SportEvent;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class SportEventController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'event_type' => ['nullable', Rule::enum(EventType::class)],
                'status' => ['nullable', Rule::enum(PublicationStatus::class)],
            ],
            [],
            [
                'q' => 'wyszukiwana fraza',
                'event_type' => 'rodzaj wydarzenia',
                'status' => 'status publikacji',
            ],
        );

        $query = SportEvent::query()
            ->withCount(['eventCompetitions', 'results'])
            ->latest('start_at');

        if (filled($filters['event_type'] ?? null)) {
            $query->where('event_type', $filters['event_type']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'ilike', "%{$search}%")
                    ->orWhere('location_name', 'ilike', "%{$search}%");
            });
        }

        $events = $query->paginate(20)->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'eventTypes' => EventType::options(),
            'statuses' => PublicationStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', $this->formOptions());
    }

    public function store(SportEventRequest $request): RedirectResponse
    {
        $event = DB::transaction(function () use ($request): SportEvent {
            $data = $request->validated();
            $competitionIds = array_map(
                'intval',
                $data['competition_ids'] ?? [],
            );

            unset($data['competition_ids']);

            $this->lockAvailableCompetitionDefinitions($competitionIds);

            $data['slug'] = UniqueSlug::for(
                SportEvent::class,
                $data['title'].' '.$data['start_at'],
            );
            $data['is_public'] = $request->boolean('is_public');
            $data['email_reminders_enabled'] = $request->boolean(
                'email_reminders_enabled',
            );
            $data['created_by'] = $request->user()->getKey();
            $data['updated_by'] = $request->user()->getKey();

            $event = SportEvent::query()->create($data);
            $event->competitions()->sync($competitionIds);

            return $event;
        });

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('success', 'Wydarzenie zostało utworzone.');
    }

    public function edit(SportEvent $sportEvent): View
    {
        $sportEvent->load('competitions');

        return view('admin.events.edit', array_merge(
            $this->formOptions($sportEvent),
            [
                'event' => $sportEvent,
                'firstEventCompetitionId' => $sportEvent->competitions
                    ->first()?->pivot?->getAttribute('id'),
            ],
        ));
    }

    public function update(
        SportEventRequest $request,
        SportEvent $sportEvent,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $sportEvent): void {
            $lockedEvent = SportEvent::query()
                ->whereKey($sportEvent->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedEvent->eventCompetitions()->lockForUpdate()->get();

            $data = $request->validated();
            $competitionIds = array_map(
                'intval',
                $data['competition_ids'] ?? [],
            );

            unset($data['competition_ids']);

            $linkedCompetitionIds = $lockedEvent->eventCompetitions()
                ->pluck('competition_definition_id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();
            $this->lockAvailableCompetitionDefinitions(
                $competitionIds,
                $linkedCompetitionIds,
            );

            $this->guardResultIntegrity(
                event: $lockedEvent,
                newEventType: $data['event_type'],
                selectedCompetitionIds: $competitionIds,
            );

            $data['is_public'] = $request->boolean('is_public');
            $data['email_reminders_enabled'] = $request->boolean(
                'email_reminders_enabled',
            );
            $data['updated_by'] = $request->user()->getKey();

            $lockedEvent->update($data);

            if (! $lockedEvent->email_reminders_enabled) {
                $lockedEvent->reminderSubscriptions()->delete();
            }

            $lockedEvent->competitions()->sync($competitionIds);
        });

        return redirect()
            ->route('admin.events.edit', $sportEvent)
            ->with('success', 'Wydarzenie zostało zapisane.');
    }

    public function destroy(SportEvent $sportEvent): RedirectResponse
    {
        DB::transaction(function () use ($sportEvent): void {
            $lockedEvent = SportEvent::query()
                ->whereKey($sportEvent->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedEvent->delete();
        });

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Wydarzenie zostało przeniesione do kosza.');
    }

    /**
     * @param  list<int>  $selectedCompetitionIds
     */
    private function guardResultIntegrity(
        SportEvent $event,
        string $newEventType,
        array $selectedCompetitionIds,
    ): void {
        $usedCompetitionIds = $event->eventCompetitions()
            ->whereHas('results')
            ->pluck('competition_definition_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($usedCompetitionIds === []) {
            return;
        }

        if ($newEventType !== EventType::Competition->value) {
            throw ValidationException::withMessages([
                'event_type' => 'Nie można zmienić zawodów na trening, ponieważ wydarzenie ma już zapisane wyniki.',
            ]);
        }

        $removedCompetitionIds = array_diff(
            $usedCompetitionIds,
            $selectedCompetitionIds,
        );

        if ($removedCompetitionIds !== []) {
            throw ValidationException::withMessages([
                'competition_ids' => 'Nie można odpiąć konkurencji, dla których zapisano wyniki. Najpierw usuń lub przenieś te wyniki.',
            ]);
        }
    }

    /**
     * @param  list<int>  $selectedCompetitionIds
     * @param  list<int>  $alreadyLinkedCompetitionIds
     */
    private function lockAvailableCompetitionDefinitions(
        array $selectedCompetitionIds,
        array $alreadyLinkedCompetitionIds = [],
    ): void {
        $selectedCompetitionIds = array_values(array_unique(
            $selectedCompetitionIds,
        ));

        if ($selectedCompetitionIds === []) {
            return;
        }

        $availableDefinitions = CompetitionDefinition::query()
            ->whereIn('id', $selectedCompetitionIds)
            ->where(function ($query) use ($alreadyLinkedCompetitionIds): void {
                $query->where('is_active', true);

                if ($alreadyLinkedCompetitionIds !== []) {
                    $query->orWhereIn('id', $alreadyLinkedCompetitionIds);
                }
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);

        if ($availableDefinitions->count() !== count($selectedCompetitionIds)) {
            throw ValidationException::withMessages([
                'competition_ids' => 'Co najmniej jedna wybrana konkurencja nie jest już dostępna. Odśwież formularz.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?SportEvent $event = null): array
    {
        $linkedCompetitionIds = $event?->competitions->modelKeys() ?? [];

        return [
            'eventTypes' => EventType::options(),
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
            'statuses' => PublicationStatus::options(),
            'competitionDefinitions' => CompetitionDefinition::query()
                ->where(function ($query) use ($linkedCompetitionIds): void {
                    $query->where('is_active', true);

                    if ($linkedCompetitionIds !== []) {
                        $query->orWhereIn('id', $linkedCompetitionIds);
                    }
                })
                ->orderBy('competition_system')
                ->orderBy('discipline')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (CompetitionDefinition $definition): string => (
                    $definition->competition_system->label()
                    .' / '
                    .$definition->discipline->label()
                )),
        ];
    }
}
