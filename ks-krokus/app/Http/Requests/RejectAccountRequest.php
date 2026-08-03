<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class RejectAccountRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return ['rejection_reason' => ['required', 'string', 'min:10', 'max:2000']];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'rejection_reason.required' => 'Podaj powód odrzucenia wniosku.',
            'rejection_reason.min' => 'Powód odrzucenia musi zawierać co najmniej 10 znaków.',
        ];
    }

    public function attributes(): array
    {
        return ['rejection_reason' => 'powód odrzucenia'];
    }
}
