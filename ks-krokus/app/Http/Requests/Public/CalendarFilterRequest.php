<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Http\Requests\LocalizedFormRequest;
use Illuminate\Validation\Rule;

final class CalendarFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'event_type' => ['nullable', Rule::enum(EventType::class)],
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'competition_system' => ['nullable', Rule::enum(CompetitionSystem::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'month' => 'miesiąc',
            'year' => 'rok',
            'event_type' => 'rodzaj wydarzenia',
            'discipline' => 'dyscyplina',
            'competition_system' => 'system rozgrywek',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'month.between' => 'Wybierz miesiąc od 1 do 12.',
            'year.between' => 'Wybierz rok od 2000 do 2100.',
        ];
    }
}
