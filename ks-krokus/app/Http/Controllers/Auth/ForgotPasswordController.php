<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

final class ForgotPasswordController extends Controller
{
    private const NEUTRAL_MESSAGE = 'Jeżeli dla podanego adresu istnieje aktywne konto, wyślemy link do ustawienia nowego hasła.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        try {
            Password::broker()->sendResetLink([
                'email' => $request->validated('email'),
                'is_active' => true,
            ]);
        } catch (Throwable $exception) {
            Log::error('Nie udało się zakolejkować wiadomości resetu hasła.', [
                'exception_class' => $exception::class,
            ]);
        }

        return back()->with('success', self::NEUTRAL_MESSAGE);
    }
}
