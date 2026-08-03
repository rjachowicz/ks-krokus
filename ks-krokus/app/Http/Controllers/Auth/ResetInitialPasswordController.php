<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetInitialPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ResetInitialPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        $email = $request->query('email');

        return view('auth.set-password', [
            'token' => $token,
            'email' => is_string($email) ? $email : '',
        ]);
    }

    public function store(ResetInitialPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => $this->statusMessage($status),
            ]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Hasło zostało ustawione. Możesz się teraz zalogować.');
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Link ustawienia hasła jest nieprawidłowy lub wygasł.',
            Password::INVALID_USER => 'Nie udało się ustawić hasła dla podanego konta.',
            Password::RESET_THROTTLED => 'Spróbuj ponownie za chwilę.',
            default => 'Nie udało się ustawić hasła. Poproś administratora o nowy link.',
        };
    }
}
