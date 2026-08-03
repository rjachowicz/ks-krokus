<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\AccountRequestStatus;
use App\Enums\UserRole;
use App\Models\AccountRequest;
use App\Models\User;
use App\Notifications\AccountRequestRejectedNotification;
use App\Notifications\SetInitialPasswordNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AccountRequestWorkflow
{
    public function approve(AccountRequest $accountRequest, User $reviewer): User
    {
        $this->authorizeAdministrator($reviewer);
        $token = null;
        $created = false;

        $user = DB::transaction(function () use ($accountRequest, $reviewer, &$token, &$created): User {
            $locked = AccountRequest::query()
                ->whereKey($accountRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $locked->status === AccountRequestStatus::Approved
                && $locked->created_user_id !== null
            ) {
                return User::withTrashed()->findOrFail($locked->created_user_id);
            }

            if ($locked->status !== AccountRequestStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Ten wniosek został już rozpatrzony.',
                ]);
            }

            $duplicateAccount = User::withTrashed()
                ->where('email', $locked->email)
                ->exists();
            $duplicateLicense = AccountRequest::query()
                ->whereKeyNot($locked->getKey())
                ->where('pzss_license_number', $locked->pzss_license_number)
                ->whereNotNull('created_user_id')
                ->exists();

            if ($duplicateAccount || $duplicateLicense) {
                throw ValidationException::withMessages([
                    'status' => 'Nie można zatwierdzić wniosku, ponieważ powiązane konto lub dane członkowskie są już używane.',
                ]);
            }

            $user = User::query()->create([
                'name' => $locked->fullName(),
                'email' => $locked->email,
                'phone' => $locked->phone,
                'password' => Str::random(64),
                'role' => UserRole::User,
                'is_active' => true,
                'is_trainer' => false,
                'has_range_access' => false,
                'show_email_publicly' => false,
                'show_phone_publicly' => false,
            ]);

            $locked->update([
                'created_user_id' => $user->getKey(),
                'status' => AccountRequestStatus::Approved,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            /** @var string $generatedToken */
            $generatedToken = Password::broker()->createToken($user);
            $token = $generatedToken;
            $created = true;

            return $user;
        });

        if ($created && is_string($token)) {
            $user->notify(new SetInitialPasswordNotification($token));
        }

        return $user;
    }

    public function reject(
        AccountRequest $accountRequest,
        User $reviewer,
        string $reason,
    ): void {
        $this->authorizeAdministrator($reviewer);

        $email = DB::transaction(function () use ($accountRequest, $reviewer, $reason): string {
            $locked = AccountRequest::query()
                ->whereKey($accountRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== AccountRequestStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Ten wniosek został już rozpatrzony.',
                ]);
            }

            $locked->update([
                'status' => AccountRequestStatus::Rejected,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'created_user_id' => null,
            ]);

            return $locked->email;
        });

        Notification::route('mail', $email)
            ->notify(new AccountRequestRejectedNotification);
    }

    private function authorizeAdministrator(User $reviewer): void
    {
        if (! $reviewer->isAdmin()) {
            throw new AuthorizationException('Tylko administrator może rozpatrywać wnioski o konto.');
        }
    }
}
