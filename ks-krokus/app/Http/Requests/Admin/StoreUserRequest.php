<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
            'is_trainer' => ['nullable', 'boolean'],
            'has_range_access' => ['nullable', 'boolean'],
            'show_email_publicly' => ['nullable', 'boolean'],
            'show_phone_publicly' => ['nullable', 'boolean'],
            'trainer_bio' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'name.required' => 'Podaj imię i nazwisko użytkownika.',
            'email.required' => 'Podaj adres e-mail użytkownika.',
            'password.required' => 'Podaj hasło użytkownika.',
            'role.required' => 'Wybierz rolę systemową.',
        ];
    }
}
