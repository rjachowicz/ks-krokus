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
        $trainers = User::query()->trainers()->get();
        $rangeAccessPeople = User::query()->rangeAccess()->get();

        return view('pages.club', compact(
            'trainers',
            'rangeAccessPeople',
        ));
    }
}
