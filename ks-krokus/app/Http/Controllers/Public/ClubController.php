<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

final class ClubController extends Controller
{
    public function __invoke(): View
    {
        $directoryPeople = User::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query
                    ->where('is_trainer', true)
                    ->orWhere('has_range_access', true);
            })
            ->orderBy('name')
            ->get();
        $trainers = $directoryPeople
            ->where('is_trainer', true)
            ->values();
        $rangeAccessPeople = $directoryPeople
            ->where('has_range_access', true)
            ->values();

        return view('pages.club', compact(
            'trainers',
            'rangeAccessPeople',
        ));
    }
}
