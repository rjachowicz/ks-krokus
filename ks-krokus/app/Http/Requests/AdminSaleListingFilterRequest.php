<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingStatus;
use Illuminate\Validation\Rule;

final class AdminSaleListingFilterRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SaleListingStatus::class)],
            'author' => ['nullable', 'integer', 'exists:users,id'],
            'category' => ['nullable', Rule::enum(SaleListingCategory::class)],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'published_from' => ['nullable', 'date'],
            'published_to' => ['nullable', 'date', 'after_or_equal:published_from'],
            'trashed' => ['nullable', Rule::in(['with', 'only'])],
        ];
    }

    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return [
            'status' => 'status',
            'author' => 'autor',
            'category' => 'kategoria',
            'created_from' => 'utworzono od',
            'created_to' => 'utworzono do',
            'published_from' => 'opublikowano od',
            'published_to' => 'opublikowano do',
            'trashed' => 'kosz',
        ];
    }
}
