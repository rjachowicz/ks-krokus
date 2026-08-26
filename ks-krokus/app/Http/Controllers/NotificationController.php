<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DeleteSelectedNotificationsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'notifications' => $user->notifications()
                ->latest()
                ->paginate(self::PER_PAGE),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $ownedNotification */
        $ownedNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($ownedNotification->unread()) {
            $ownedNotification->markAsRead();
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Powiadomienie zostało oznaczone jako przeczytane.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $updated = $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        $message = $updated === 0
            ? 'Nie masz nieprzeczytanych powiadomień.'
            : 'Wszystkie powiadomienia zostały oznaczone jako przeczytane.';

        return redirect()
            ->route('notifications.index')
            ->with('success', $message);
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $ownedNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $ownedNotification->delete();

        return $this->redirectToIndex($request)
            ->with('success', 'Powiadomienie zostało usunięte.');
    }

    public function destroySelected(DeleteSelectedNotificationsRequest $request): RedirectResponse
    {
        $deleted = DB::transaction(
            fn (): int => $request->user()
                ->notifications()
                ->whereKey($request->notificationIds())
                ->delete(),
        );

        $message = $deleted === 0
            ? 'Nie znaleziono wybranych powiadomień do usunięcia.'
            : "Usunięto wybrane powiadomienia: {$deleted}.";

        return $this->redirectToIndex($request)
            ->with('success', $message);
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $deleted = DB::transaction(
            fn (): int => $request->user()->notifications()->delete(),
        );

        $message = $deleted === 0
            ? 'Nie masz powiadomień do usunięcia.'
            : "Usunięto wszystkie Twoje powiadomienia: {$deleted}.";

        return redirect()
            ->route('notifications.index')
            ->with('success', $message);
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        $requestedPage = $request->input('page');
        $page = is_int($requestedPage) || (is_string($requestedPage) && ctype_digit($requestedPage))
            ? max(1, (int) $requestedPage)
            : 1;
        $remaining = $request->user()->notifications()->count();
        $lastPage = max(1, (int) ceil($remaining / self::PER_PAGE));
        $page = min($page, $lastPage);

        return redirect()->route(
            'notifications.index',
            $page > 1 ? ['page' => $page] : [],
        );
    }
}
