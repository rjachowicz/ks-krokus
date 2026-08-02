<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SaleListingStatus;
use Illuminate\Validation\Rule;

final class MySaleListingFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['status' => ['nullable', Rule::enum(SaleListingStatus::class)]];
    }

    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return ['status' => 'status'];
    }
}
