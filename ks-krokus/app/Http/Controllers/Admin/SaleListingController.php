<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingCondition;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSaleListingFilterRequest;
use App\Http\Requests\FlagSaleListingRequest;
use App\Http\Requests\RejectSaleListingRequest;
use App\Http\Requests\UpdateSaleListingRequest;
use App\Models\SaleListing;
use App\Models\User;
use App\Support\SaleListingPersistence;
use App\Support\SaleListingWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class SaleListingController extends Controller
{
    public function index(AdminSaleListingFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = SaleListing::query()
            ->with(['author', 'primaryImage'])
            ->withCount(['reports as pending_reports_count' => fn ($builder) => $builder->where('status', 'pending')])
            ->latest();

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        } elseif (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        foreach (['status' => 'status', 'author' => 'user_id', 'category' => 'category'] as $filter => $column) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($column, $filters[$filter]);
            }
        }

        $this->applyDateRange($query, 'created_at', $filters['created_from'] ?? null, $filters['created_to'] ?? null);
        $this->applyDateRange($query, 'published_at', $filters['published_from'] ?? null, $filters['published_to'] ?? null);

        $counts = SaleListing::query()
            ->selectRaw('status, COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.sale-listings.index', [
            'listings' => $query->paginate(20)->withQueryString(),
            'statuses' => SaleListingStatus::options(),
            'categories' => SaleListingCategory::options(),
            'authors' => User::query()->whereHas('saleListings')->orderBy('name')->get(['id', 'name']),
            'counts' => $counts,
        ]);
    }

    public function edit(SaleListing $saleListing): View
    {
        Gate::authorize('view', $saleListing);
        $saleListing->load([
            'author', 'approver', 'rejecter', 'images',
            'moderations.actor',
            'reports' => fn ($query) => $query->where('status', 'pending'),
        ]);

        return view('admin.sale-listings.edit', [
            'listing' => $saleListing,
            'categories' => SaleListingCategory::options(),
            'types' => SaleListingFirearmType::options(),
            'conditions' => SaleListingCondition::options(),
            'maxImages' => (int) config('listings.max_images'),
            'maxImageSizeKb' => (int) config('listings.image_max_size_kb'),
            'formContext' => 'moderation',
        ]);
    }

    public function update(
        UpdateSaleListingRequest $request,
        SaleListing $saleListing,
        SaleListingPersistence $persistence,
    ): RedirectResponse {
        $updated = $persistence->update($request, $saleListing);

        return redirect()
            ->route('admin.sale-listings.edit', $updated)
            ->with('success', 'Treść ogłoszenia została zapisana.');
    }

    public function approve(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('approve', $saleListing);

        if (! $saleListing->images()->exists()) {
            throw ValidationException::withMessages(['images' => 'Nie można zatwierdzić ogłoszenia bez zdjęcia.']);
        }

        $this->runLocked($saleListing, fn (SaleListing $locked) => $workflow->approve($locked, auth()->user()));

        return back()->with('success', 'Ogłoszenie zostało zatwierdzone i opublikowane.');
    }

    public function reject(
        RejectSaleListingRequest $request,
        SaleListing $saleListing,
        SaleListingWorkflow $workflow,
    ): RedirectResponse {
        $this->runLocked(
            $saleListing,
            fn (SaleListing $locked) => $workflow->reject(
                $locked,
                $request->user(),
                $request->validated('rejection_reason'),
            ),
        );

        return back()->with('success', 'Ogłoszenie zostało odrzucone, a autor otrzymał powód.');
    }

    public function hide(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('hide', $saleListing);
        $this->runLocked($saleListing, fn (SaleListing $locked) => $workflow->hide($locked, auth()->user()));

        return back()->with('success', 'Ogłoszenie zostało ukryte.');
    }

    public function unhide(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('unhide', $saleListing);
        $this->runLocked($saleListing, fn (SaleListing $locked) => $workflow->unhide($locked, auth()->user()));

        return back()->with('success', 'Widoczność ogłoszenia została przywrócona.');
    }

    public function flag(
        FlagSaleListingRequest $request,
        SaleListing $saleListing,
        SaleListingWorkflow $workflow,
    ): RedirectResponse {
        $this->runLocked(
            $saleListing,
            fn (SaleListing $locked) => $workflow->flag($locked, $request->user(), $request->validated('note')),
        );

        return back()->with('success', 'Ogłoszenie zostało zgłoszone administratorowi.');
    }

    public function sold(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('markAsSold', $saleListing);
        $this->runLocked($saleListing, fn (SaleListing $locked) => $workflow->markAsSold($locked, auth()->user()));

        return back()->with('success', 'Ogłoszenie zostało oznaczone jako sprzedane.');
    }

    public function archive(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('archive', $saleListing);
        $this->runLocked($saleListing, fn (SaleListing $locked) => $workflow->archive($locked, auth()->user()));

        return back()->with('success', 'Ogłoszenie zostało zarchiwizowane.');
    }

    public function destroy(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('delete', $saleListing);

        $this->runLocked($saleListing, function (SaleListing $locked) use ($workflow): void {
            $workflow->deleted($locked, auth()->user());
            $locked->delete();
        });

        return redirect()->route('admin.sale-listings.index')->with('success', 'Ogłoszenie zostało przeniesione do kosza.');
    }

    public function restore(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('restore', $saleListing);

        DB::transaction(function () use ($saleListing, $workflow): void {
            $locked = SaleListing::withTrashed()->whereKey($saleListing->getKey())->lockForUpdate()->firstOrFail();
            $locked->restore();
            $workflow->restored($locked, auth()->user());
        });

        return redirect()->route('admin.sale-listings.edit', $saleListing)->with('success', 'Ogłoszenie zostało przywrócone z kosza.');
    }

    private function applyDateRange($query, string $column, mixed $from, mixed $to): void
    {
        if (filled($from)) {
            $query->whereDate($column, '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate($column, '<=', $to);
        }
    }

    private function runLocked(SaleListing $listing, callable $callback): void
    {
        DB::transaction(function () use ($listing, $callback): void {
            $locked = SaleListing::query()->whereKey($listing->getKey())->lockForUpdate()->firstOrFail();
            $callback($locked);
        });
    }
}
