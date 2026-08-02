<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SaleListing;
use Illuminate\Validation\Validator;

final class SubmitSaleListingRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('saleListing');

        return $listing instanceof SaleListing
            && $this->user()?->can('submit', $listing) === true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var SaleListing $listing */
            $listing = $this->route('saleListing');

            if ($listing->images()->count() < 1) {
                $validator->errors()->add('images', 'Dodaj co najmniej jedno zdjęcie przed wysłaniem ogłoszenia.');
            }

            if (mb_strlen(trim($listing->description)) < 30) {
                $validator->errors()->add('description', 'Opis musi mieć co najmniej 30 znaków.');
            }
        });
    }

    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return ['images' => 'zdjęcia', 'description' => 'opis'];
    }
}
