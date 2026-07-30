<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EventType;
use App\Enums\ResultStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventResultRequest;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventResultController extends Controller
{
    public function index(Request $request): View
    {
        $query = EventResult::query()
            ->with([
                'eventCompetition.event',
                'eventCompetition.competition',
                'user',
            ])
            ->latest();

        if ($request->filled('event_id')) {
            $eventId = (int) $request->integer('event_id');

            $query->whereHas(
                'eventCompetition',
                fn ($builder) => $builder->where(
                    'sport_event_id',
                    $eventId,
                ),
            );
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where('participant_name', 'like', "%{$search}%");
        }

        $results = $query->paginate(30)->withQueryString();

        return view('admin.results.index', [
            'results' => $results,
            'events' => \App\Models\SportEvent::query()
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
        $data = $this->normalizeData($request);

        $result = EventResult::query()->create([
            ...$data,
            'entered_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('admin.results.edit', $result)
            ->with('success', 'Wynik został dodany.');
    }

    public function edit(EventResult $eventResult): View
    {
        $eventResult->load([
            'eventCompetition.event',
            'eventCompetition.competition',
            'user',
        ]);

        return view('admin.results.edit', array_merge(
            $this->formOptions(),
            ['result' => $eventResult],
        ));
    }

    public function update(
        EventResultRequest $request,
        EventResult $eventResult,
    ): RedirectResponse {
        $data = $this->normalizeData($request, $eventResult);
        $data['updated_by'] = $request->user()->getKey();

        $eventResult->update($data);

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
    private function formOptions(): array
    {
        $eventCompetitions = EventCompetition::query()
            ->whereHas(
                'event',
                fn ($query) => $query->where(
                    'event_type',
                    EventType::Competition->value,
                ),
            )
            ->with(['event', 'competition'])
            ->get()
            ->sortByDesc(
                fn (EventCompetition $item) => $item->event->start_at,
            );

        return [
            'eventCompetitions' => $eventCompetitions,
            'users' => User::query()->orderBy('name')->get(),
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

        $eventCompetition = EventCompetition::query()
            ->with('event')
            ->findOrFail($data['event_competition_id']);

        abort_unless(
            $eventCompetition->event->event_type === EventType::Competition,
            422,
            'Wyniki mogą być przypisywane wyłącznie do zawodów.',
        );

        if (! empty($data['user_id'])) {
            $user = User::query()->findOrFail($data['user_id']);
            $sameLinkedUser = $existingResult?->user_id === $user->getKey();

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
