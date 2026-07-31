<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ClubPosition;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ClubPositionRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        /** @var ClubPosition|null $position */
        $position = $this->route('clubPosition');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('club_positions', 'slug')->ignore($position),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(
                    fn (Builder $query) => $query->whereNull('deleted_at'),
                ),
            ],
            'user_sort_orders' => ['nullable', 'array'],
            'user_sort_orders.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'name.required' => 'Podaj nazwę funkcji klubowej.',
            'sort_order.required' => 'Podaj kolejność funkcji.',
        ];
    }
}
