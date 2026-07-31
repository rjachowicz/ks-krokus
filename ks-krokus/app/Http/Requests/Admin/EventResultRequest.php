<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\EventType;
use App\Enums\ResultStatus;
use App\Models\EventResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class EventResultRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        /** @var EventResult|null $result */
        $result = $this->route('eventResult');
        $currentEventCompetitionId = $result?->event_competition_id;
        $currentUserId = $result?->user_id;

        $availableEventCompetition = Rule::exists(
            'event_competitions',
            'id',
        )->where(function (Builder $query) use ($currentEventCompetitionId): void {
            $query->where(function (Builder $availability) use ($currentEventCompetitionId): void {
                $availability->whereExists(function (Builder $events): void {
                    $events
                        ->selectRaw('1')
                        ->from('sport_events')
                        ->whereColumn(
                            'sport_events.id',
                            'event_competitions.sport_event_id',
                        )
                        ->whereNull('sport_events.deleted_at')
                        ->where(
                            'sport_events.event_type',
                            EventType::Competition->value,
                        );
                });

                if ($currentEventCompetitionId !== null) {
                    $availability->orWhere(
                        'event_competitions.id',
                        $currentEventCompetitionId,
                    );
                }
            });
        });

        return [
            'event_competition_id' => [
                'required',
                'integer',
                $availableEventCompetition,
            ],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function (Builder $query) use ($currentUserId): void {
                    $query->where(function (Builder $availableUser) use ($currentUserId): void {
                        $availableUser->whereNull('deleted_at');

                        if ($currentUserId !== null) {
                            $availableUser->orWhere('id', $currentUserId);
                        }
                    });
                }),
            ],
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

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'event_competition_id.required' => 'Wybierz wydarzenie i konkurencję.',
            'event_competition_id.exists' => 'Wybierz konkurencję należącą do istniejących zawodów.',
            'user_id.exists' => 'Wybrany użytkownik nie istnieje lub został usunięty.',
            'participant_name.required_without' => 'Podaj zawodnika lub wybierz powiązanego użytkownika.',
            'score.required' => 'Podaj wynik.',
            'status.required' => 'Wybierz status wyniku.',
        ];
    }
}
