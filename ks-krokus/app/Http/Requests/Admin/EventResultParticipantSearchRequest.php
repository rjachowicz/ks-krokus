<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

final class EventResultParticipantSearchRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'q.required' => 'Wpisz co najmniej 2 znaki.',
            'q.min' => 'Wpisz co najmniej 2 znaki.',
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'q' => 'wyszukiwany zawodnik',
        ];
    }
}
