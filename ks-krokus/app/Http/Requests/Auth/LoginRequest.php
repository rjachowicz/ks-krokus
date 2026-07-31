<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\LocalizedFormRequest;

final class LoginRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (! is_string($email)) {
            return;
        }

        $this->merge([
            'email' => mb_strtolower(trim($email)),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'adres e-mail',
            'password' => 'hasło',
            'remember' => 'zapamiętaj mnie',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'email.required' => 'Podaj adres e-mail.',
            'password.required' => 'Podaj hasło.',
        ];
    }
}
