<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Models\SportEvent;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class SportEventRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        /** @var SportEvent|null $event */
        $event = $this->route('sportEvent');
        $linkedCompetitionIds = $event?->competitions()
            ->pluck('competition_definitions.id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all() ?? [];

        $availableCompetition = Rule::exists(
            'competition_definitions',
            'id',
        )->where(function (Builder $query) use ($linkedCompetitionIds): void {
            $query->where(function (Builder $availability) use ($linkedCompetitionIds): void {
                $availability->where('is_active', true);

                if ($linkedCompetitionIds !== []) {
                    $availability->orWhereIn('id', $linkedCompetitionIds);
                }
            });
        });

        return [
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::enum(EventType::class)],
            'description' => ['nullable', 'string', 'max:30000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'competition_system' => ['nullable', Rule::enum(CompetitionSystem::class)],
            'status' => ['required', Rule::enum(PublicationStatus::class)],
            'is_public' => ['nullable', 'boolean'],
            'registration_url' => ['nullable', 'url', 'max:2048'],
            'competition_ids' => ['nullable', 'array'],
            'competition_ids.*' => [
                'integer',
                'distinct',
                $availableCompetition,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'title.required' => 'Podaj nazwę wydarzenia.',
            'event_type.required' => 'Wybierz rodzaj wydarzenia.',
            'start_at.required' => 'Podaj datę rozpoczęcia wydarzenia.',
            'location_name.required' => 'Podaj miejsce wydarzenia.',
            'status.required' => 'Wybierz status publikacji.',
        ];
    }
}
