<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SaleListing;

final class StoreSaleListingRequest extends SaleListingFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SaleListing::class) === true;
    }
}
