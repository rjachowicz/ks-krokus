<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

final class UpdateOwnPasswordRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:web'],
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
            'current_password.required' => 'Podaj aktualne hasło.',
            'current_password.current_password' => 'Aktualne hasło jest nieprawidłowe.',
            'password.required' => 'Podaj nowe hasło.',
            'password.confirmed' => 'Potwierdzenie nowego hasła nie jest zgodne.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'aktualne hasło',
            'password' => 'nowe hasło',
            'password_confirmation' => 'potwierdzenie nowego hasła',
        ];
    }
}
