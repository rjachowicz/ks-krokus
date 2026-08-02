<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingReportReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSaleListingFilterRequest;
use App\Http\Requests\ReportSaleListingRequest;
use App\Models\SaleListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class SaleListingController extends Controller
{
    public function index(PublicSaleListingFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = SaleListing::query()
            ->publiclyVisible()
            ->with('primaryImage');

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('manufacturer', 'ilike', "%{$search}%")
                    ->orWhere('model', 'ilike', "%{$search}%");
            });
        }

        foreach (['category' => 'category', 'type' => 'firearm_type', 'caliber' => 'caliber'] as $filter => $column) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($column, $filters[$filter]);
            }
        }

        if (isset($filters['price_from'])) {
            $query->where('price', '>=', $filters['price_from']);
        }

        if (isset($filters['price_to'])) {
            $query->where('price', '<=', $filters['price_to']);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('published_at')->orderBy('id'),
            'price_asc' => $query->orderByRaw('price IS NULL')->orderBy('price')->latest('published_at'),
            'price_desc' => $query->orderByRaw('price IS NULL')->orderByDesc('price')->latest('published_at'),
            default => $query->latest('published_at')->latest('id'),
        };

        $listings = $query->paginate(12)->withQueryString();
        $calibers = SaleListing::query()
            ->publiclyVisible()
            ->whereNotNull('caliber')
            ->where('caliber', '!=', '')
            ->distinct()
            ->orderBy('caliber')
            ->pluck('caliber');

        return view('listings.index', [
            'listings' => $listings,
            'categories' => SaleListingCategory::options(),
            'types' => SaleListingFirearmType::options(),
            'calibers' => $calibers,
        ]);
    }

    public function show(SaleListing $saleListing): View
    {
        abort_unless($saleListing->isPubliclyVisible(), 404);

        $saleListing->load(['author', 'images']);
        SaleListing::query()->whereKey($saleListing->getKey())->increment('view_count');

        return view('listings.show', [
            'listing' => $saleListing,
            'reportReasons' => SaleListingReportReason::options(),
        ]);
    }

    public function report(
        ReportSaleListingRequest $request,
        SaleListing $saleListing,
    ): RedirectResponse {
        abort_unless($saleListing->isPubliclyVisible(), 404);

        $identity = $request->user() !== null
            ? 'user:'.$request->user()->getKey()
            : 'guest:'.($request->ip() ?? 'unknown').'|'.mb_substr((string) $request->userAgent(), 0, 255);
        $fingerprint = hash_hmac('sha256', $identity, (string) config('app.key'));

        DB::transaction(function () use ($request, $saleListing, $fingerprint): void {
            $saleListing->reports()->firstOrCreate(
                [
                    'reporter_hash' => $fingerprint,
                    'reason' => $request->validated('reason'),
                ],
                [
                    'reporter_id' => $request->user()?->getKey(),
                    'details' => $request->validated('details'),
                    'status' => 'pending',
                ],
            );
        });

        return back()->with('success', 'Dziękujemy. Zgłoszenie zostało przekazane administratorowi.');
    }
}
