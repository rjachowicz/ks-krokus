<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class UpdateOwnEmailRequest extends LocalizedFormRequest
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
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email_current_password' => ['required', 'current_password:web'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'email.required' => 'Podaj nowy adres e-mail.',
            'email.unique' => 'Nie można zapisać tego adresu e-mail.',
            'email_current_password.required' => 'Podaj aktualne hasło.',
            'email_current_password.current_password' => 'Aktualne hasło jest nieprawidłowe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'adres e-mail',
            'email_current_password' => 'aktualne hasło',
        ];
    }
}
