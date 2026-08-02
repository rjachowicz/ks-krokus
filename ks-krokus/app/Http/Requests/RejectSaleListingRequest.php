<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SaleListing;

final class RejectSaleListingRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('saleListing');

        return $listing instanceof SaleListing
            && $this->user()?->can('reject', $listing) === true;
    }

    public function rules(): array
    {
        return ['rejection_reason' => ['required', 'string', 'min:10', 'max:2000']];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'rejection_reason.required' => 'Podaj powód odrzucenia ogłoszenia.',
            'rejection_reason.min' => 'Powód odrzucenia musi mieć co najmniej 10 znaków.',
        ];
    }

    public function attributes(): array
    {
        return ['rejection_reason' => 'powód odrzucenia'];
    }
}
