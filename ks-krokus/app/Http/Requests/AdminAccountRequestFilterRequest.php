<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AccountRequestStatus;
use App\Enums\Discipline;
use Illuminate\Validation\Rule;

final class AdminAccountRequestFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(AccountRequestStatus::class)],
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
        ];
    }

    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return [
            'q' => 'wyszukiwana fraza',
            'status' => 'status',
            'discipline' => 'dyscyplina',
            'created_from' => 'data złożenia od',
            'created_to' => 'data złożenia do',
        ];
    }
}
