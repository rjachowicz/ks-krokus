<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeEventReminderRequest;
use App\Models\SportEvent;
use App\Models\User;
use App\Support\EventReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class EventReminderController extends Controller
{
    public function store(
        SubscribeEventReminderRequest $request,
        SportEvent $sportEvent,
        EventReminderService $service,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        try {
            $created = $service->subscribe($user, $sportEvent);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('calendar.show', $sportEvent)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('calendar.show', $sportEvent)
            ->with(
                'success',
                $created
                    ? 'Przypomnienie o wydarzeniu zostało ustawione.'
                    : 'Przypomnienie o tym wydarzeniu jest już ustawione.',
            );
    }

    public function destroy(
        Request $request,
        SportEvent $sportEvent,
        EventReminderService $service,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $service->unsubscribe($user, $sportEvent);

        return redirect()
            ->route('calendar.show', $sportEvent)
            ->with('success', 'Przypomnienie o wydarzeniu zostało anulowane.');
    }
}
