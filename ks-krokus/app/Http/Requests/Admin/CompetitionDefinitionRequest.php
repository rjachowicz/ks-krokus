<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Models\CompetitionDefinition;
use Illuminate\Validation\Rule;

class CompetitionDefinitionRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $code = $this->input('code');

        if (! is_string($code)) {
            return;
        }

        $this->merge([
            'code' => mb_strtoupper(trim($code)),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        /** @var CompetitionDefinition|null $definition */
        $definition = $this->route('competitionDefinition');

        return [
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('competition_definitions', 'code')->ignore($definition),
            ],
            'name' => ['required', 'string', 'max:255'],
            'discipline' => ['required', Rule::enum(Discipline::class)],
            'competition_system' => ['required', Rule::enum(CompetitionSystem::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'code.required' => 'Podaj kod konkurencji.',
            'name.required' => 'Podaj nazwę konkurencji.',
            'discipline.required' => 'Wybierz dyscyplinę.',
            'competition_system.required' => 'Wybierz system rozgrywek.',
        ];
    }
}
