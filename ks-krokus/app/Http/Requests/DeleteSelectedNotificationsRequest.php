<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class DeleteSelectedNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'select_visible' => $this->boolean('select_visible'),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'notifications' => ['nullable', 'array'],
            'notifications.*' => ['bail', 'required', 'uuid', 'distinct'],
            'select_visible' => ['required', 'boolean'],
            'visible_notifications' => ['nullable', 'array'],
            'visible_notifications.*' => ['bail', 'required', 'uuid', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notifications.array' => 'Wybór powiadomień ma nieprawidłowy format.',
            'notifications.*.required' => 'Identyfikator wybranego powiadomienia jest wymagany.',
            'notifications.*.uuid' => 'Identyfikator wybranego powiadomienia jest nieprawidłowy.',
            'notifications.*.distinct' => 'Każde powiadomienie może być wybrane tylko raz.',
            'select_visible.required' => 'Informacja o zaznaczeniu widocznych powiadomień jest wymagana.',
            'select_visible.boolean' => 'Informacja o zaznaczeniu widocznych powiadomień jest nieprawidłowa.',
            'visible_notifications.array' => 'Lista widocznych powiadomień ma nieprawidłowy format.',
            'visible_notifications.*.required' => 'Identyfikator widocznego powiadomienia jest wymagany.',
            'visible_notifications.*.uuid' => 'Identyfikator widocznego powiadomienia jest nieprawidłowy.',
            'visible_notifications.*.distinct' => 'Każde widoczne powiadomienie może wystąpić tylko raz.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'notifications' => 'wybrane powiadomienia',
            'notifications.*' => 'wybrane powiadomienie',
            'select_visible' => 'zaznaczenie wszystkich widocznych',
            'visible_notifications' => 'widoczne powiadomienia',
            'visible_notifications.*' => 'widoczne powiadomienie',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $selected = $this->input('notifications', []);
                $visible = $this->input('visible_notifications', []);

                if ($this->boolean('select_visible')) {
                    if (! is_array($visible) || $visible === []) {
                        $validator->errors()->add(
                            'notifications',
                            'Na tej stronie nie ma powiadomień, które można usunąć.',
                        );
                    }

                    return;
                }

                if (! is_array($selected) || $selected === []) {
                    $validator->errors()->add(
                        'notifications',
                        'Zaznacz co najmniej jedno powiadomienie do usunięcia.',
                    );
                }
            },
        ];
    }

    /**
     * @return list<string>
     */
    public function notificationIds(): array
    {
        $key = $this->boolean('select_visible')
            ? 'visible_notifications'
            : 'notifications';

        /** @var list<string> $ids */
        $ids = $this->validated($key, []);

        return $ids;
    }
}
