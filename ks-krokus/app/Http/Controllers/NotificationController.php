<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'notifications' => $user->notifications()
                ->latest()
                ->paginate(15),
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
}
