<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

final class ResetInitialPasswordRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => mb_strtolower(trim($email))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() === null;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'token.required' => 'Link ustawienia hasła jest nieprawidłowy.',
            'email.required' => 'W linku brakuje adresu e-mail.',
            'password.required' => 'Podaj nowe hasło.',
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'token',
            'email' => 'adres e-mail',
            'password' => 'hasło',
        ];
    }
}
