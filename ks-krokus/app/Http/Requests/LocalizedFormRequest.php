<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class LocalizedFormRequest extends FormRequest
{
    /**
     * Prevent malformed array values from breaking Blade when they are flashed
     * back and rendered through old() in scalar form controls.
     */
    protected function failedValidation(Validator $validator): void
    {
        $input = $this->input();
        $arrayFields = $this->oldInputArrayFields();

        foreach ($input as $field => $value) {
            if (! is_array($value)) {
                continue;
            }

            $input[$field] = in_array($field, $arrayFields, true)
                ? $this->sanitizeArrayForOldInput($field, $value)
                : '';
        }

        $this->replace($input);
        request()->replace($input);

        parent::failedValidation($validator);
    }

    /**
     * @return list<string>
     */
    protected function oldInputArrayFields(): array
    {
        return [];
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    private function sanitizeArrayForOldInput(
        string $field,
        array $values,
    ): array {
        foreach ($values as $key => $value) {
            if ($field === 'existing_images' && is_array($value)) {
                foreach ($value as $attribute => $attributeValue) {
                    if (
                        ! is_scalar($attributeValue)
                        && $attributeValue !== null
                    ) {
                        $value[$attribute] = '';
                    }
                }

                $values[$key] = $value;

                continue;
            }

            if (! is_scalar($value) && $value !== null) {
                $values[$key] = '';
            }
        }

        return $values;
    }

    public function messages(): array
    {
        return [
            'required' => 'Uzupełnij pole :attribute.',
            'required_without' => 'Uzupełnij pole :attribute, jeśli pole :values pozostaje puste.',
            'confirmed' => 'Potwierdzenie pola :attribute nie jest zgodne.',
            'email' => 'Podaj prawidłowy adres e-mail.',
            'url' => 'Podaj prawidłowy adres URL.',
            'date' => 'Podaj prawidłową datę w polu :attribute.',
            'after_or_equal' => 'Pole :attribute nie może wskazywać daty wcześniejszej niż :date.',
            'string' => 'Pole :attribute musi być tekstem.',
            'integer' => 'Pole :attribute musi być liczbą całkowitą.',
            'in' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
            'boolean' => 'Pole :attribute ma nieprawidłową wartość.',
            'array' => 'Pole :attribute ma nieprawidłowy format.',
            'image' => 'Wybrany plik w polu :attribute musi być obrazem.',
            'mimes' => 'Wybrany plik musi być w jednym z formatów: :values.',
            'uploaded' => 'Nie udało się przesłać pliku w polu :attribute.',
            'max.string' => 'Pole :attribute może zawierać maksymalnie :max znaków.',
            'max.numeric' => 'Wartość pola :attribute nie może być większa niż :max.',
            'max.file' => 'Wybrany plik jest zbyt duży. Maksymalny rozmiar to :max KB.',
            'max.array' => 'Pole :attribute może zawierać maksymalnie :max elementów.',
            'min.string' => 'Pole :attribute musi zawierać co najmniej :min znaków.',
            'min.numeric' => 'Wartość pola :attribute nie może być mniejsza niż :min.',
            'unique' => 'Podana wartość pola :attribute jest już używana.',
            'exists' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
            'distinct' => 'Pole :attribute zawiera powtarzające się wartości.',
            'enum' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
            'prohibited' => 'Formularz został odrzucony. Spróbuj ponownie.',
            'password.letters' => 'Hasło musi zawierać co najmniej jedną literę.',
            'password.mixed' => 'Hasło musi zawierać małą i wielką literę.',
            'password.numbers' => 'Hasło musi zawierać co najmniej jedną cyfrę.',
            'password.min' => 'Hasło musi zawierać co najmniej :min znaków.',
        ];
    }
}
