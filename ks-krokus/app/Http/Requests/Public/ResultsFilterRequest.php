<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Http\Requests\LocalizedFormRequest;
use Illuminate\Validation\Rule;

final class ResultsFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'competition_system' => ['nullable', Rule::enum(CompetitionSystem::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'wyszukiwana fraza',
            'discipline' => 'dyscyplina',
            'competition_system' => 'system rozgrywek',
        ];
    }
}
