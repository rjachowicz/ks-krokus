<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\SportEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'event_type' => ['nullable', 'string'],
            'discipline' => ['nullable', 'string'],
            'competition_system' => ['nullable', 'string'],
        ]);

        $displayDate = Carbon::create(
            (int) ($validated['year'] ?? now()->year),
            (int) ($validated['month'] ?? now()->month),
            1,
            0,
            0,
            0,
            config('app.timezone'),
        );

        $query = SportEvent::query()
            ->publiclyVisible()
            ->with('competitions')
            ->where('start_at', '<=', $displayDate->copy()->endOfMonth())
            ->where(function ($builder) use ($displayDate): void {
                $builder
                    ->whereNull('end_at')
                    ->where('start_at', '>=', $displayDate->copy()->startOfMonth())
                    ->orWhere('end_at', '>=', $displayDate->copy()->startOfMonth());
            })
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

        $events = $query->get();
        $calendarStart = $displayDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $displayDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = collect();

        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $days->push($day->copy());
        }

        return view('calendar.index', [
            'events' => $events,
            'eventsByDate' => $events->groupBy(fn (SportEvent $event): string => $event->start_at->toDateString()),
            'days' => $days,
            'displayDate' => $displayDate,
            'previousMonth' => $displayDate->copy()->subMonth(),
            'nextMonth' => $displayDate->copy()->addMonth(),
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
