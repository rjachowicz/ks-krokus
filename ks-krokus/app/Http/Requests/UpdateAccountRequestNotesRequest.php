<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class UpdateAccountRequestNotesRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return ['internal_notes' => ['nullable', 'string', 'max:5000']];
    }

    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return ['internal_notes' => 'notatki wewnętrzne'];
    }
}
