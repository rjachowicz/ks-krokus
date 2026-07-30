<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ClubPosition;
use App\Models\User;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function __invoke(): View
    {
        $positions = ClubPosition::query()
            ->active()
            ->with('users')
            ->get();

        $trainers = User::query()
            ->trainers()
            ->get();

        return view('pages.contact', compact('positions', 'trainers'));
    }
}
