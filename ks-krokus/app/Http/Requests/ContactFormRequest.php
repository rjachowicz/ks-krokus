<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ContactFormRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'imię i nazwisko',
            'email' => 'adres e-mail',
            'phone' => 'telefon',
            'subject' => 'temat',
            'message' => 'wiadomość',
            'website' => 'strona internetowa',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'name.required' => 'Podaj imię i nazwisko.',
            'email.required' => 'Podaj adres e-mail.',
            'subject.required' => 'Podaj temat wiadomości.',
            'message.required' => 'Wpisz treść wiadomości.',
            'message.min' => 'Wiadomość musi zawierać co najmniej :min znaków.',
        ];
    }
}
