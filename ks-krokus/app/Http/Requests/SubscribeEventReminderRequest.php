<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SportEvent;

final class SubscribeEventReminderRequest extends LocalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'event_reminder_current_password' => [
                'required',
                'current_password:web',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'event_reminder_current_password.required' => 'Podaj aktualne hasło, aby ustawić przypomnienie.',
            'event_reminder_current_password.current_password' => 'Aktualne hasło jest nieprawidłowe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'event_reminder_current_password' => 'aktualne hasło',
        ];
    }

    protected function getRedirectUrl(): string
    {
        /** @var SportEvent $sportEvent */
        $sportEvent = $this->route('sportEvent');

        return route('calendar.show', $sportEvent);
    }
}
