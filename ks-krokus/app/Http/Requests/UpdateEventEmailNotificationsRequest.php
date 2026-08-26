<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class UpdateEventEmailNotificationsRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'event_email_notifications_enabled' => $this->boolean(
                'event_email_notifications_enabled',
            ),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'event_email_notifications_enabled' => ['required', 'boolean'],
            'event_notifications_current_password' => [
                Rule::requiredIf($this->boolean('event_email_notifications_enabled')),
                'nullable',
                'current_password:web',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'event_notifications_current_password.required' => 'Podaj aktualne hasło, aby włączyć przypomnienia e-mail.',
            'event_notifications_current_password.current_password' => 'Aktualne hasło jest nieprawidłowe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'event_email_notifications_enabled' => 'zgoda na e-mailowe przypomnienia o wydarzeniach',
            'event_notifications_current_password' => 'aktualne hasło',
        ];
    }
}
