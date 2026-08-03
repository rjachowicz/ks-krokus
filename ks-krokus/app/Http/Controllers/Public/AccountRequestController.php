<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\Discipline;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\AccountRequest;
use App\Models\User;
use App\Notifications\AccountRequestSubmittedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Throwable;

final class AccountRequestController extends Controller
{
    private const SUCCESS_MESSAGE = 'Wniosek został przyjęty do weryfikacji. Po zatwierdzeniu otrzymasz wiadomość z instrukcją ustawienia hasła.';

    public function create(): View
    {
        return view('account-requests.create', [
            'disciplines' => Discipline::options(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');
        $data['data_processing_consent'] = true;

        try {
            if ($this->isDuplicate($data['email'], $data['pzss_license_number'])) {
                return $this->successResponse();
            }

            DB::transaction(function () use ($data): void {
                $accountRequest = AccountRequest::query()->create($data);
                $administrators = User::query()
                    ->where('role', UserRole::Admin->value)
                    ->where('is_active', true)
                    ->get();

                Notification::send(
                    $administrators,
                    new AccountRequestSubmittedNotification($accountRequest),
                );
            });
        } catch (Throwable $exception) {
            if (
                $exception instanceof QueryException
                && (string) $exception->getCode() === '23505'
            ) {
                return $this->successResponse();
            }

            Log::error('Nie udało się zapisać wniosku o konto.', [
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'account_request' => 'Nie udało się zapisać wniosku. Spróbuj ponownie później.',
                ]);
        }

        return $this->successResponse();
    }

    private function isDuplicate(string $email, string $licenseNumber): bool
    {
        return User::withTrashed()->where('email', $email)->exists()
            || AccountRequest::query()
                ->where(function ($query) use ($email, $licenseNumber): void {
                    $query->where('email', $email)
                        ->orWhere('pzss_license_number', $licenseNumber);
                })
                ->exists();
    }

    private function successResponse(): RedirectResponse
    {
        return redirect()
            ->route('account-requests.create')
            ->with('success', self::SUCCESS_MESSAGE);
    }
}
