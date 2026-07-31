<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ResultStatus;
use Illuminate\Validation\Rule;

class EventResultRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        return [
            'event_competition_id' => [
                'required',
                'integer',
                'exists:event_competitions,id',
            ],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'participant_name' => [
                'nullable',
                'required_without:user_id',
                'string',
                'max:255',
            ],
            'club_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'score' => ['required', 'string', 'max:64'],
            'place' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'classification' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ResultStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
