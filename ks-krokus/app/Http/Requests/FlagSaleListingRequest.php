<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SaleListing;

final class FlagSaleListingRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('saleListing');

        return $listing instanceof SaleListing
            && $this->user()?->can('flag', $listing) === true;
    }

    public function rules(): array
    {
        return ['note' => ['required', 'string', 'min:10', 'max:2000']];
    }

    public function messages(): array
    {
        return [...parent::messages(), 'note.required' => 'Opisz, dlaczego ogłoszenie wymaga uwagi administratora.'];
    }

    public function attributes(): array
    {
        return ['note' => 'uzasadnienie zgłoszenia'];
    }
}
