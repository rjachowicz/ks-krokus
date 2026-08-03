<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ForgotPasswordRequest extends LocalizedFormRequest
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
        return ['email' => ['required', 'email', 'max:255']];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'email.required' => 'Podaj adres e-mail przypisany do konta.',
        ];
    }

    public function attributes(): array
    {
        return ['email' => 'adres e-mail'];
    }
}
