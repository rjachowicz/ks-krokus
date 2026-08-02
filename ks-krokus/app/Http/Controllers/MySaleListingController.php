<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingCondition;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingStatus;
use App\Http\Requests\MySaleListingFilterRequest;
use App\Http\Requests\StoreSaleListingRequest;
use App\Http\Requests\SubmitSaleListingRequest;
use App\Http\Requests\UpdateSaleListingRequest;
use App\Models\SaleListing;
use App\Support\SaleListingPersistence;
use App\Support\SaleListingWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class MySaleListingController extends Controller
{
    public function index(MySaleListingFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = SaleListing::query()
            ->where('user_id', $request->user()->getKey())
            ->with('primaryImage')
            ->latest();

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return view('my-listings.index', [
            'listings' => $query->paginate(15)->withQueryString(),
            'statuses' => SaleListingStatus::options(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', SaleListing::class);

        return view('my-listings.create', $this->formOptions());
    }

    public function store(
        StoreSaleListingRequest $request,
        SaleListingPersistence $persistence,
    ): RedirectResponse {
        $listing = $persistence->create($request);

        return redirect()
            ->route('admin.my-listings.index')
            ->with('success', $listing->status === SaleListingStatus::Pending
                ? 'Ogłoszenie zostało wysłane do moderacji.'
                : 'Szkic ogłoszenia został zapisany.');
    }

    public function edit(SaleListing $saleListing): View
    {
        Gate::authorize('update', $saleListing);
        abort_unless($saleListing->user_id === auth()->id(), 403);
        $saleListing->load('images');

        return view('my-listings.edit', [
            ...$this->formOptions(),
            'listing' => $saleListing,
        ]);
    }

    public function update(
        UpdateSaleListingRequest $request,
        SaleListing $saleListing,
        SaleListingPersistence $persistence,
    ): RedirectResponse {
        abort_unless($saleListing->user_id === $request->user()->getKey(), 403);
        $updated = $persistence->update($request, $saleListing);

        return redirect()
            ->route('admin.my-listings.index')
            ->with('success', $updated->status === SaleListingStatus::Pending
                ? 'Zmiany zapisano, a ogłoszenie trafiło do moderacji.'
                : 'Ogłoszenie zostało zapisane.');
    }

    public function destroy(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('delete', $saleListing);
        abort_unless($saleListing->user_id === auth()->id(), 403);

        DB::transaction(function () use ($saleListing, $workflow): void {
            $locked = SaleListing::query()->whereKey($saleListing->getKey())->lockForUpdate()->firstOrFail();
            $workflow->deleted($locked, auth()->user());
            $locked->delete();
        });

        return redirect()->route('admin.my-listings.index')->with('success', 'Ogłoszenie zostało przeniesione do kosza.');
    }

    public function submit(
        SubmitSaleListingRequest $request,
        SaleListing $saleListing,
        SaleListingWorkflow $workflow,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $saleListing, $workflow): void {
            $locked = SaleListing::query()->whereKey($saleListing->getKey())->lockForUpdate()->firstOrFail();
            $workflow->submit($locked, $request->user());
        });

        return back()->with('success', 'Ogłoszenie zostało wysłane do moderacji.');
    }

    public function sold(SaleListing $saleListing, SaleListingWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('markAsSold', $saleListing);

        DB::transaction(function () use ($saleListing, $workflow): void {
            $locked = SaleListing::query()->whereKey($saleListing->getKey())->lockForUpdate()->firstOrFail();
            $workflow->markAsSold($locked, auth()->user());
        });

        return back()->with('success', 'Ogłoszenie zostało oznaczone jako sprzedane.');
    }

    public function duplicate(
        SaleListing $saleListing,
        SaleListingPersistence $persistence,
    ): RedirectResponse {
        abort_unless($saleListing->user_id === auth()->id(), 403);
        Gate::authorize('view', $saleListing);
        $copy = $persistence->duplicate($saleListing, auth()->user());

        return redirect()
            ->route('admin.my-listings.edit', $copy)
            ->with('success', 'Utworzono nowy szkic na podstawie ogłoszenia.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'categories' => SaleListingCategory::options(),
            'types' => SaleListingFirearmType::options(),
            'conditions' => SaleListingCondition::options(),
            'maxImages' => (int) config('listings.max_images'),
            'maxImageSizeKb' => (int) config('listings.image_max_size_kb'),
            'formContext' => 'owner',
        ];
    }
}
