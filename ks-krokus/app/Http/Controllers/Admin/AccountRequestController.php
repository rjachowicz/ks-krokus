<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountRequestStatus;
use App\Enums\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAccountRequestFilterRequest;
use App\Http\Requests\RejectAccountRequest;
use App\Http\Requests\UpdateAccountRequestNotesRequest;
use App\Models\AccountRequest;
use App\Support\AccountRequestWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AccountRequestController extends Controller
{
    public function index(AdminAccountRequestFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = AccountRequest::query()
            ->with(['reviewer', 'createdUser'])
            ->latest();

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('pzss_license_number', 'ilike', "%{$search}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['discipline'] ?? null)) {
            $query->whereJsonContains('disciplines', $filters['discipline']);
        }

        if (filled($filters['created_from'] ?? null)) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (filled($filters['created_to'] ?? null)) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        $counts = AccountRequest::query()
            ->selectRaw('status, COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.account-requests.index', [
            'accountRequests' => $query->paginate(20)->withQueryString(),
            'statuses' => AccountRequestStatus::options(),
            'disciplines' => Discipline::options(),
            'counts' => $counts,
        ]);
    }

    public function show(AccountRequest $accountRequest): View
    {
        $accountRequest->load(['reviewer', 'createdUser']);

        return view('admin.account-requests.show', [
            'accountRequest' => $accountRequest,
            'disciplines' => Discipline::options(),
        ]);
    }

    public function updateNotes(
        UpdateAccountRequestNotesRequest $request,
        AccountRequest $accountRequest,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $accountRequest): void {
            AccountRequest::query()
                ->whereKey($accountRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->update($request->validated());
        });

        return back()->with('success', 'Notatki wewnętrzne zostały zapisane.');
    }

    public function approve(
        AccountRequest $accountRequest,
        AccountRequestWorkflow $workflow,
    ): RedirectResponse {
        $user = $workflow->approve($accountRequest, auth()->user());

        if (! $user->wasRecentlyCreated) {
            return redirect()
                ->route('admin.account-requests.show', $accountRequest)
                ->with('success', 'Wniosek był już zatwierdzony. Nie utworzono kolejnego konta ani nowego tokenu.');
        }

        return redirect()
            ->route('admin.account-requests.show', $accountRequest)
            ->with('success', 'Wniosek został zatwierdzony, konto utworzone, a link ustawienia hasła wysłany do użytkownika '.$user->name.'.');
    }

    public function reject(
        RejectAccountRequest $request,
        AccountRequest $accountRequest,
        AccountRequestWorkflow $workflow,
    ): RedirectResponse {
        $workflow->reject(
            $accountRequest,
            $request->user(),
            $request->validated('rejection_reason'),
        );

        return redirect()
            ->route('admin.account-requests.show', $accountRequest)
            ->with('success', 'Wniosek został odrzucony. Wnioskodawca otrzymał neutralną wiadomość.');
    }
}
