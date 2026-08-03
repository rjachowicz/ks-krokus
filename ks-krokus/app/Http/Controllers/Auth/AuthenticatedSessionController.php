<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => 'Podany e-mail lub hasło są nieprawidłowe.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $defaultDestination = $request->user()->canManageContent()
            ? route('admin.dashboard')
            : route('account.show');

        return redirect()->intended($defaultDestination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
