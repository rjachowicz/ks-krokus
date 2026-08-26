<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\EventType;
use App\Enums\IpscDivision;
use App\Enums\MemberAgeCategory;
use App\Enums\PublicationStatus;
use App\Enums\ResultStatus;
use App\Models\EventResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EventResultRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['participant_name', 'club_name', 'category', 'score', 'classification', 'notes'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = trim($value) ?: null;
            }
        }

        if (isset($normalized['category'])) {
            $normalized['category'] = MemberAgeCategory::fromStoredValue(
                $normalized['category'],
            )?->value ?? $normalized['category'];
        }

        if (isset($normalized['classification'])) {
            $normalized['classification'] = IpscDivision::fromStoredValue(
                $normalized['classification'],
            )?->value ?? $normalized['classification'];
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        /** @var EventResult|null $result */
        $result = $this->route('eventResult');

        return $result instanceof EventResult
            ? Gate::allows('update', $result)
            : $this->user()?->canManageContent() === true;
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
                        )
                        ->where(
                            'sport_events.status',
                            PublicationStatus::Published->value,
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
                        $availableUser
                            ->whereNull('deleted_at')
                            ->where('is_active', true);

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
            'category' => [
                'nullable',
                'string',
                'max:255',
                Rule::in($this->allowedAgeCategories($result)),
            ],
            'score' => ['required', 'string', 'max:64'],
            'place' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'classification' => [
                'nullable',
                'string',
                'max:255',
                Rule::in($this->allowedIpscDivisions($result)),
            ],
            'status' => ['required', Rule::enum(ResultStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return list<string> */
    private function allowedAgeCategories(?EventResult $result): array
    {
        $values = array_column(MemberAgeCategory::cases(), 'value');
        $historical = $result?->category;

        if (
            filled($historical)
            && MemberAgeCategory::fromStoredValue($historical) === null
        ) {
            $values[] = $historical;
        }

        return $values;
    }

    /** @return list<string> */
    private function allowedIpscDivisions(?EventResult $result): array
    {
        $values = array_column(IpscDivision::cases(), 'value');
        $historical = $result?->classification;

        if (filled($historical) && IpscDivision::fromStoredValue($historical) === null) {
            $values[] = $historical;
        }

        return $values;
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
