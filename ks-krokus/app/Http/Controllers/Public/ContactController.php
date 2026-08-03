<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactMessage;
use App\Models\ClubPosition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

final class ContactController extends Controller
{
    public function __invoke(): View
    {
        $positions = ClubPosition::query()
            ->active()
            ->with([
                'users' => fn ($query) => $query->where('users.is_active', true),
            ])
            ->get();

        $trainers = User::query()
            ->trainers()
            ->get();

        return view('pages.contact', compact('positions', 'trainers'));
    }

    public function send(ContactFormRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');

        try {
            Mail::to((string) config('contact.recipient'))
                ->queue(new ContactMessage($data));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'Nie udało się wysłać wiadomości. Spróbuj ponownie później.',
                ]);
        }

        return redirect()
            ->to(route('contact').'#formularz-kontaktowy')
            ->with('success', 'Dziękujemy. Wiadomość została przyjęta do wysłania.');
    }
}
