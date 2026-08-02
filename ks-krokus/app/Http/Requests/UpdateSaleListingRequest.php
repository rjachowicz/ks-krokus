<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SaleListing;

final class UpdateSaleListingRequest extends SaleListingFormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('saleListing');

        return $listing instanceof SaleListing
            && $this->user()?->can('update', $listing) === true;
    }
}
