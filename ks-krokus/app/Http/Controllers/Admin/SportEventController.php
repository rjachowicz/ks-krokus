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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class SportEventController extends Controller
{
    public function index(Request $request): View
    {
        $query = SportEvent::query()
            ->withCount(['eventCompetitions', 'results'])
            ->latest('start_at');

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->string('event_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%");
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

            $data['slug'] = UniqueSlug::for(
                SportEvent::class,
                $data['title'].' '.$data['start_at'],
            );
            $data['is_public'] = $request->boolean('is_public');
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
            $this->formOptions(),
            ['event' => $sportEvent],
        ));
    }

    public function update(
        SportEventRequest $request,
        SportEvent $sportEvent,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $sportEvent): void {
            $data = $request->validated();
            $competitionIds = array_map(
                'intval',
                $data['competition_ids'] ?? [],
            );

            unset($data['competition_ids']);

            $this->guardResultIntegrity(
                event: $sportEvent,
                newEventType: $data['event_type'],
                selectedCompetitionIds: $competitionIds,
            );

            $data['is_public'] = $request->boolean('is_public');
            $data['updated_by'] = $request->user()->getKey();

            $sportEvent->update($data);
            $sportEvent->competitions()->sync($competitionIds);
        });

        return redirect()
            ->route('admin.events.edit', $sportEvent)
            ->with('success', 'Wydarzenie zostało zapisane.');
    }

    public function destroy(SportEvent $sportEvent): RedirectResponse
    {
        $sportEvent->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Wydarzenie zostało przeniesione do kosza.');
    }

    /**
     * @param list<int> $selectedCompetitionIds
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
                'competition_ids' => 'Nie można odpiąć konkurencji, dla których zapisano już wyniki. Najpierw usuń lub przenieś te wyniki.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'eventTypes' => EventType::options(),
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
            'statuses' => PublicationStatus::options(),
            'competitionDefinitions' => CompetitionDefinition::query()
                ->active()
                ->get()
                ->groupBy(fn (CompetitionDefinition $definition): string => (
                    $definition->competition_system->label()
                    .' / '
                    .$definition->discipline->label()
                )),
        ];
    }
}
