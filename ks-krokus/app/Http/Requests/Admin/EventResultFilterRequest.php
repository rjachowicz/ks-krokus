<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\EventType;
use App\Enums\ResultStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

final class EventResultFilterRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('sport_events', 'id')->where(
                    fn (Builder $query) => $query->where(
                        'event_type',
                        EventType::Competition->value,
                    ),
                ),
            ],
            'event_competition_id' => [
                'nullable',
                'integer',
                Rule::exists('event_competitions', 'id'),
            ],
            'status' => ['nullable', Rule::enum(ResultStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'q' => 'wyszukiwany zawodnik',
            'event_id' => 'wydarzenie',
            'event_competition_id' => 'konkurencja wydarzenia',
            'status' => 'status wyniku',
        ];
    }
}
