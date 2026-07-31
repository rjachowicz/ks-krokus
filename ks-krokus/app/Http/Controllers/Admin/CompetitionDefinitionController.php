<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompetitionDefinitionRequest;
use App\Models\CompetitionDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CompetitionDefinitionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(
            [
                'discipline' => ['nullable', Rule::enum(Discipline::class)],
                'competition_system' => ['nullable', Rule::enum(CompetitionSystem::class)],
            ],
            [],
            [
                'discipline' => 'dyscyplina',
                'competition_system' => 'system rozgrywek',
            ],
        );

        $query = CompetitionDefinition::query()
            ->orderBy('competition_system')
            ->orderBy('discipline')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (filled($filters['discipline'] ?? null)) {
            $query->where('discipline', $filters['discipline']);
        }

        if (filled($filters['competition_system'] ?? null)) {
            $query->where(
                'competition_system',
                $filters['competition_system'],
            );
        }

        $definitions = $query->paginate(30)->withQueryString();

        return view('admin.competitions.index', [
            'definitions' => $definitions,
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.competitions.create', [
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
        ]);
    }

    public function store(
        CompetitionDefinitionRequest $request,
    ): RedirectResponse {
        $data = $request->validated();
        $data['code'] = mb_strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        $definition = CompetitionDefinition::query()->create($data);

        return redirect()
            ->route('admin.competitions.edit', $definition)
            ->with('success', 'Konkurencja została dodana.');
    }

    public function edit(
        CompetitionDefinition $competitionDefinition,
    ): View {
        return view('admin.competitions.edit', [
            'definition' => $competitionDefinition,
            'disciplines' => Discipline::options(),
            'systems' => CompetitionSystem::options(),
        ]);
    }

    public function update(
        CompetitionDefinitionRequest $request,
        CompetitionDefinition $competitionDefinition,
    ): RedirectResponse {
        $data = $request->validated();
        $data['code'] = mb_strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        $competitionDefinition->update($data);

        return back()->with('success', 'Konkurencja została zapisana.');
    }

    public function destroy(
        CompetitionDefinition $competitionDefinition,
    ): RedirectResponse {
        if ($competitionDefinition->eventCompetitions()->exists()) {
            return back()->withErrors([
                'competition' => 'Nie można usunąć konkurencji użytej w kalendarzu lub wynikach. Wyłącz ją zamiast usuwać.',
            ]);
        }

        $competitionDefinition->delete();

        return redirect()
            ->route('admin.competitions.index')
            ->with('success', 'Konkurencja została usunięta.');
    }
}
