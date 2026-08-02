<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleListingReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class SaleListingReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('admin.sale-listings.reports', [
            'reports' => SaleListingReport::query()
                ->with(['listing', 'reporter'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function resolve(Request $request, SaleListingReport $report): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        DB::transaction(function () use ($request, $report): void {
            SaleListingReport::query()
                ->whereKey($report->getKey())
                ->where('status', 'pending')
                ->lockForUpdate()
                ->update([
                    'status' => 'reviewed',
                    'reviewed_by' => $request->user()->getKey(),
                    'reviewed_at' => now(),
                ]);
        });

        return back()->with('success', 'Zgłoszenie zostało oznaczone jako rozpatrzone.');
    }
}
