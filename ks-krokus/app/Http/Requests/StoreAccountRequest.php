<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Discipline;
use Illuminate\Validation\Rule;

final class StoreAccountRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ([
            'first_name',
            'last_name',
            'email',
            'phone',
            'pzss_license_number',
            'patent_number',
            'firearm_permit_number',
            'member_number',
            'additional_information',
        ] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = trim($value);
            }
        }

        if (isset($normalized['email'])) {
            $normalized['email'] = mb_strtolower($normalized['email']);
        }

        if (isset($normalized['pzss_license_number'])) {
            $normalized['pzss_license_number'] = mb_strtoupper($normalized['pzss_license_number']);
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return $this->user() === null;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:120'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'pzss_license_number' => ['required', 'string', 'max:100'],
            'pzss_license_expires_at' => ['required', 'date'],
            'patent_number' => ['required', 'string', 'max:100'],
            'firearm_permit_number' => ['nullable', 'string', 'max:100'],
            'member_number' => ['nullable', 'string', 'max:100'],
            'joined_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'disciplines' => ['required', 'array', 'min:1', 'max:3'],
            'disciplines.*' => ['required', 'string', 'distinct', Rule::enum(Discipline::class)],
            'additional_information' => ['nullable', 'string', 'max:5000'],
            'data_processing_consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'first_name.required' => 'Podaj imię.',
            'last_name.required' => 'Podaj nazwisko.',
            'email.required' => 'Podaj adres e-mail.',
            'phone.required' => 'Podaj numer telefonu.',
            'birth_date.required' => 'Podaj datę urodzenia.',
            'birth_date.before_or_equal' => 'Data urodzenia nie może przypadać w przyszłości.',
            'pzss_license_number.required' => 'Podaj numer licencji PZSS.',
            'pzss_license_expires_at.required' => 'Podaj datę ważności licencji PZSS.',
            'patent_number.required' => 'Podaj numer patentu.',
            'disciplines.required' => 'Wybierz co najmniej jedną dyscyplinę.',
            'disciplines.min' => 'Wybierz co najmniej jedną dyscyplinę.',
            'data_processing_consent.accepted' => 'Aby złożyć wniosek, zaznacz zgodę na przetwarzanie danych.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'imię',
            'last_name' => 'nazwisko',
            'email' => 'adres e-mail',
            'phone' => 'telefon',
            'birth_date' => 'data urodzenia',
            'pzss_license_number' => 'numer licencji PZSS',
            'pzss_license_expires_at' => 'data ważności licencji PZSS',
            'patent_number' => 'numer patentu',
            'firearm_permit_number' => 'numer pozwolenia',
            'member_number' => 'numer członkowski',
            'joined_year' => 'rok wstąpienia',
            'disciplines' => 'dyscypliny',
            'disciplines.*' => 'dyscyplina',
            'additional_information' => 'dodatkowe informacje',
            'data_processing_consent' => 'zgoda na przetwarzanie danych',
            'website' => 'strona internetowa',
        ];
    }

    /** @return list<string> */
    protected function oldInputArrayFields(): array
    {
        return ['disciplines'];
    }
}
