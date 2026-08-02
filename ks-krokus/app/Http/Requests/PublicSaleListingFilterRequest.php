<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingFirearmType;
use Illuminate\Validation\Rule;

final class PublicSaleListingFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(SaleListingCategory::class)],
            'type' => ['nullable', Rule::enum(SaleListingFirearmType::class)],
            'caliber' => ['nullable', 'string', 'max:64'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0', 'gte:price_from'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'price_asc', 'price_desc'])],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'price_from.min' => 'Cena minimalna nie może być ujemna.',
            'price_to.min' => 'Cena maksymalna nie może być ujemna.',
            'price_to.gte' => 'Cena maksymalna nie może być niższa od minimalnej.',
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'wyszukiwana fraza',
            'category' => 'kategoria',
            'type' => 'rodzaj',
            'caliber' => 'kaliber',
            'price_from' => 'cena od',
            'price_to' => 'cena do',
            'sort' => 'sortowanie',
        ];
    }
}
