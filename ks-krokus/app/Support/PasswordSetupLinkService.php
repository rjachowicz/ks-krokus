<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use App\Notifications\SetInitialPasswordNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final class PasswordSetupLinkService
{
    public function send(User $user, User $sender, bool $accountApproved = false): void
    {
        if (! $sender->isAdmin()) {
            throw new AuthorizationException('Tylko administrator może wysyłać link ustawienia hasła.');
        }

        DB::transaction(function () use ($user, $sender, $accountApproved): void {
            $locked = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! $locked->is_active) {
                throw ValidationException::withMessages([
                    'password_link' => 'Link można wysłać wyłącznie dla istniejącego, aktywnego konta.',
                ]);
            }

            /** @var string $token */
            $token = Password::broker()->createToken($locked);

            $locked->forceFill([
                'password_link_sent_by' => $sender->getKey(),
                'password_link_sent_at' => now(),
            ])->save();

            $locked->notify(new SetInitialPasswordNotification($token, $accountApproved));
        });
    }
}
