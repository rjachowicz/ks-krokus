<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ClubPositionController;
use App\Http\Controllers\Admin\CompetitionDefinitionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventResultController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SportEventController as AdminSportEventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Public\CalendarController;
use App\Http\Controllers\Public\ClubController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\ResultsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/aktualnosci', [NewsController::class, 'index'])
    ->name('news.index');
Route::get('/aktualnosci/{post}', [NewsController::class, 'show'])
    ->name('news.show');

Route::get('/kalendarz', [CalendarController::class, 'index'])
    ->name('calendar.index');
Route::get('/kalendarz/{sportEvent}', [CalendarController::class, 'show'])
    ->name('calendar.show');

Route::get('/wyniki', [ResultsController::class, 'index'])
    ->name('results.index');
Route::get('/wyniki/{sportEvent}', [ResultsController::class, 'show'])
    ->name('results.show');

Route::get('/klub', ClubController::class)->name('club');
Route::get('/kontakt', ContactController::class)->name('contact');
Route::view('/regulamin', 'pages.rules')->name('rules');
Route::view('/rodo', 'pages.rodo')->name('rodo');

Route::permanentRedirect('/informacje-klubowe', '/klub')
    ->name('club.legacy');

Route::middleware('guest')->group(function (): void {
    Route::get('/logowanie', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/logowanie', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::post('/wylogowanie', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('panel')
    ->name('admin.')
    ->middleware(['auth', 'active'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)
            ->name('dashboard');

        Route::middleware(
            'role:' . UserRole::Admin->value . ',' . UserRole::Moderator->value,
        )->group(function (): void {
            Route::resource('aktualnosci', AdminPostController::class)
                ->except('show')
                ->parameters(['aktualnosci' => 'post'])
                ->names([
                    'index' => 'posts.index',
                    'create' => 'posts.create',
                    'store' => 'posts.store',
                    'edit' => 'posts.edit',
                    'update' => 'posts.update',
                    'destroy' => 'posts.destroy',
                ]);

            Route::resource('wydarzenia', AdminSportEventController::class)
                ->except('show')
                ->parameters(['wydarzenia' => 'sportEvent'])
                ->names([
                    'index' => 'events.index',
                    'create' => 'events.create',
                    'store' => 'events.store',
                    'edit' => 'events.edit',
                    'update' => 'events.update',
                    'destroy' => 'events.destroy',
                ]);

            Route::resource('wyniki', EventResultController::class)
                ->except('show')
                ->parameters(['wyniki' => 'eventResult'])
                ->names([
                    'index' => 'results.index',
                    'create' => 'results.create',
                    'store' => 'results.store',
                    'edit' => 'results.edit',
                    'update' => 'results.update',
                    'destroy' => 'results.destroy',
                ]);
        });

        Route::middleware('role:' . UserRole::Admin->value)
            ->group(function (): void {
                Route::resource('uzytkownicy', UserController::class)
                    ->except('show')
                    ->parameters(['uzytkownicy' => 'user'])
                    ->names([
                        'index' => 'users.index',
                        'create' => 'users.create',
                        'store' => 'users.store',
                        'edit' => 'users.edit',
                        'update' => 'users.update',
                        'destroy' => 'users.destroy',
                    ]);

                Route::resource('funkcje-klubowe', ClubPositionController::class)
                    ->except('show')
                    ->parameters(['funkcje-klubowe' => 'clubPosition'])
                    ->names([
                        'index' => 'positions.index',
                        'create' => 'positions.create',
                        'store' => 'positions.store',
                        'edit' => 'positions.edit',
                        'update' => 'positions.update',
                        'destroy' => 'positions.destroy',
                    ]);

                Route::resource(
                    'konkurencje',
                    CompetitionDefinitionController::class,
                )
                    ->except('show')
                    ->parameters([
                        'konkurencje' => 'competitionDefinition',
                    ])
                    ->names([
                        'index' => 'competitions.index',
                        'create' => 'competitions.create',
                        'store' => 'competitions.store',
                        'edit' => 'competitions.edit',
                        'update' => 'competitions.update',
                        'destroy' => 'competitions.destroy',
                    ]);
            });
    });
