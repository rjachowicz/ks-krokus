<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class UpdateOwnProfileRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['name', 'phone'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = trim($value);
            }
        }

        $normalized['show_email_publicly'] = $this->boolean('show_email_publicly');
        $normalized['show_phone_publicly'] = $this->boolean('show_phone_publicly');

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'show_email_publicly' => ['required', 'boolean'],
            'show_phone_publicly' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'name.required' => 'Podaj imię i nazwisko.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'imię i nazwisko',
            'phone' => 'telefon',
            'show_email_publicly' => 'zgoda na publiczne pokazanie adresu e-mail',
            'show_phone_publicly' => 'zgoda na publiczne pokazanie telefonu',
        ];
    }
}
