<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SaleListingReportReason;
use Illuminate\Validation\Rule;

final class ReportSaleListingRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(SaleListingReportReason::class)],
            'details' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [...parent::messages(), 'reason.required' => 'Wybierz powód zgłoszenia.'];
    }

    public function attributes(): array
    {
        return ['reason' => 'powód zgłoszenia', 'details' => 'dodatkowe informacje', 'website' => 'pole kontrolne'];
    }
}
