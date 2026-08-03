<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Discipline;
use App\Http\Requests\UpdateOwnEmailRequest;
use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Http\Requests\UpdateOwnProfileRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AccountController extends Controller
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['memberProfile.verifier']);

        return view('account.show', [
            'user' => $user,
            'disciplines' => Discipline::options(),
        ]);
    }

    public function updateProfile(UpdateOwnProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Dane profilu zostały zapisane.');
    }

    public function updateEmail(UpdateOwnEmailRequest $request): RedirectResponse
    {
        try {
            $request->user()->update([
                'email' => $request->validated('email'),
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'email' => 'Nie można zapisać tego adresu e-mail.',
            ]);
        }

        return back()->with('success', 'Adres e-mail został zmieniony.');
    }

    public function updatePassword(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => Hash::make($request->validated('password')),
            'remember_token' => Str::random(60),
        ])->save();

        return back()->with('success', 'Hasło zostało zmienione.');
    }
}
