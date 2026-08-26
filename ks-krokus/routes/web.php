<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AccountRequestController as AdminAccountRequestController;
use App\Http\Controllers\Admin\ClubPositionController;
use App\Http\Controllers\Admin\CompetitionDefinitionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventResultController;
use App\Http\Controllers\Admin\MemberProfileController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SaleListingController as AdminSaleListingController;
use App\Http\Controllers\Admin\SaleListingReportController;
use App\Http\Controllers\Admin\SportEventController as AdminSportEventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetInitialPasswordController;
use App\Http\Controllers\EventReminderController;
use App\Http\Controllers\MySaleListingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\AccountRequestController as PublicAccountRequestController;
use App\Http\Controllers\Public\CalendarController;
use App\Http\Controllers\Public\ClubController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\ResultsController;
use App\Http\Controllers\Public\SaleListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/aktualnosci', [NewsController::class, 'index'])
    ->name('news.index');
Route::get('/aktualnosci/{slug}', [NewsController::class, 'show'])
    ->name('news.show');

Route::get('/kalendarz', [CalendarController::class, 'index'])
    ->name('calendar.index');
Route::get('/kalendarz/{slug}/podglad', [CalendarController::class, 'modal'])
    ->name('calendar.modal');
Route::get('/kalendarz/{slug}', [CalendarController::class, 'show'])
    ->name('calendar.show');

Route::prefix('kalendarz/{sportEvent}/przypomnienie')
    ->middleware(['auth', 'active', 'throttle:event-reminders'])
    ->group(function (): void {
        Route::post('/', [EventReminderController::class, 'store'])
            ->name('event-reminders.store');
        Route::delete('/', [EventReminderController::class, 'destroy'])
            ->name('event-reminders.destroy');
    });

Route::get('/wyniki', [ResultsController::class, 'index'])
    ->name('results.index');
Route::get('/wyniki/{slug}', [ResultsController::class, 'show'])
    ->name('results.show');

Route::get('/ogloszenia', [SaleListingController::class, 'index'])
    ->name('listings.index');
Route::get('/ogloszenia/{saleListing}', [SaleListingController::class, 'show'])
    ->name('listings.show');
Route::post('/ogloszenia/{saleListing}/zglos', [SaleListingController::class, 'report'])
    ->middleware('throttle:listing-reports')
    ->name('listings.report');

Route::get('/klub', ClubController::class)->name('club');
Route::get('/kontakt', ContactController::class)->name('contact');
Route::post('/kontakt', [ContactController::class, 'send'])
    ->middleware('throttle:contact')
    ->name('contact.send');
Route::view('/regulamin', 'pages.rules')->name('rules');
Route::view('/rodo', 'pages.rodo')->name('rodo');

Route::permanentRedirect('/informacje-klubowe', '/klub')
    ->name('club.legacy');

Route::middleware('guest')->group(function (): void {
    Route::get('/wniosek-o-konto', [PublicAccountRequestController::class, 'create'])
        ->name('account-requests.create');
    Route::post('/wniosek-o-konto', [PublicAccountRequestController::class, 'store'])
        ->middleware('throttle:account-requests')
        ->name('account-requests.store');

    Route::get('/logowanie', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/logowanie', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/nie-pamietam-hasla', [ForgotPasswordController::class, 'create'])
        ->name('password.request');
    Route::post('/nie-pamietam-hasla', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('/ustaw-haslo/{token}', [ResetInitialPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/ustaw-haslo', [ResetInitialPasswordController::class, 'store'])
        ->middleware('throttle:password-update')
        ->name('password.update');
});

Route::post('/wylogowanie', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('powiadomienia')
    ->name('notifications.')
    ->middleware(['auth', 'active'])
    ->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{notification}/przeczytane', [NotificationController::class, 'markAsRead'])
            ->middleware('throttle:notification-actions')
            ->name('read');
        Route::post('/przeczytaj-wszystkie', [NotificationController::class, 'markAllAsRead'])
            ->middleware('throttle:notification-actions')
            ->name('read-all');
        Route::delete('/zaznaczone', [NotificationController::class, 'destroySelected'])
            ->middleware('throttle:notification-actions')
            ->name('destroy-selected');
        Route::delete('/wszystkie', [NotificationController::class, 'destroyAll'])
            ->middleware('throttle:notification-actions')
            ->name('destroy-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->whereUuid('notification')
            ->middleware('throttle:notification-actions')
            ->name('destroy');
    });

Route::prefix('moje-konto')
    ->name('account.')
    ->middleware(['auth', 'active'])
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'show'])->name('show');
        Route::patch('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/email', [AccountController::class, 'updateEmail'])->name('email.update');
        Route::put('/haslo', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::patch('/przypomnienia-wydarzen', [AccountController::class, 'updateEventEmailNotifications'])
            ->name('event-notifications.update');
    });

Route::prefix('panel')
    ->name('admin.')
    ->middleware(['auth', 'active'])
    ->group(function (): void {
        Route::get('moje-konto', [AccountController::class, 'show'])
            ->name('account.show');
        Route::patch('moje-konto/profil', [AccountController::class, 'updateProfile'])
            ->name('account.profile.update');
        Route::patch('moje-konto/email', [AccountController::class, 'updateEmail'])
            ->name('account.email.update');
        Route::put('moje-konto/haslo', [AccountController::class, 'updatePassword'])
            ->name('account.password.update');
        Route::patch('moje-konto/przypomnienia-wydarzen', [AccountController::class, 'updateEventEmailNotifications'])
            ->name('account.event-notifications.update');

        Route::get('/', DashboardController::class)
            ->name('dashboard');

        Route::get('moje-ogloszenia', [MySaleListingController::class, 'index'])
            ->name('my-listings.index');
        Route::get('moje-ogloszenia/nowe', [MySaleListingController::class, 'create'])
            ->name('my-listings.create');
        Route::post('moje-ogloszenia', [MySaleListingController::class, 'store'])
            ->name('my-listings.store');
        Route::get('moje-ogloszenia/{saleListing}/edytuj', [MySaleListingController::class, 'edit'])
            ->name('my-listings.edit');
        Route::put('moje-ogloszenia/{saleListing}', [MySaleListingController::class, 'update'])
            ->name('my-listings.update');
        Route::delete('moje-ogloszenia/{saleListing}', [MySaleListingController::class, 'destroy'])
            ->name('my-listings.destroy');
        Route::post('moje-ogloszenia/{saleListing}/wyslij', [MySaleListingController::class, 'submit'])
            ->name('my-listings.submit');
        Route::post('moje-ogloszenia/{saleListing}/sprzedane', [MySaleListingController::class, 'sold'])
            ->name('my-listings.sold');
        Route::post('moje-ogloszenia/{saleListing}/kopiuj', [MySaleListingController::class, 'duplicate'])
            ->name('my-listings.duplicate');

        Route::middleware(
            'role:'.UserRole::Admin->value.','.UserRole::Moderator->value,
        )->group(function (): void {
            Route::get('ogloszenia', [AdminSaleListingController::class, 'index'])
                ->name('sale-listings.index');
            Route::get('ogloszenia/{saleListing}/edytuj', [AdminSaleListingController::class, 'edit'])
                ->name('sale-listings.edit');
            Route::put('ogloszenia/{saleListing}', [AdminSaleListingController::class, 'update'])
                ->name('sale-listings.update');
            Route::post('ogloszenia/{saleListing}/ukryj', [AdminSaleListingController::class, 'hide'])
                ->name('sale-listings.hide');
            Route::post('ogloszenia/{saleListing}/pokaz', [AdminSaleListingController::class, 'unhide'])
                ->name('sale-listings.unhide');
            Route::post('ogloszenia/{saleListing}/zglos-administratorowi', [AdminSaleListingController::class, 'flag'])
                ->name('sale-listings.flag');

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

        Route::middleware('role:'.UserRole::Admin->value)
            ->group(function (): void {
                Route::post('ogloszenia/{saleListing}/zatwierdz', [AdminSaleListingController::class, 'approve'])
                    ->name('sale-listings.approve');
                Route::post('ogloszenia/{saleListing}/odrzuc', [AdminSaleListingController::class, 'reject'])
                    ->name('sale-listings.reject');
                Route::post('ogloszenia/{saleListing}/sprzedane', [AdminSaleListingController::class, 'sold'])
                    ->name('sale-listings.sold');
                Route::post('ogloszenia/{saleListing}/archiwizuj', [AdminSaleListingController::class, 'archive'])
                    ->name('sale-listings.archive');
                Route::delete('ogloszenia/{saleListing}', [AdminSaleListingController::class, 'destroy'])
                    ->name('sale-listings.destroy');
                Route::post('ogloszenia/{saleListing}/przywroc', [AdminSaleListingController::class, 'restore'])
                    ->withTrashed()
                    ->name('sale-listings.restore');
                Route::get('ogloszenia-zgloszenia', [SaleListingReportController::class, 'index'])
                    ->name('sale-listings.reports.index');
                Route::post('ogloszenia-zgloszenia/{report}/rozpatrz', [SaleListingReportController::class, 'resolve'])
                    ->name('sale-listings.reports.resolve');

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

                Route::get('wnioski-o-konto', [AdminAccountRequestController::class, 'index'])
                    ->name('account-requests.index');
                Route::get('wnioski-o-konto/{accountRequest}', [AdminAccountRequestController::class, 'show'])
                    ->name('account-requests.show');
                Route::put('wnioski-o-konto/{accountRequest}/notatki', [AdminAccountRequestController::class, 'updateNotes'])
                    ->name('account-requests.notes.update');
                Route::post('wnioski-o-konto/{accountRequest}/zatwierdz', [AdminAccountRequestController::class, 'approve'])
                    ->name('account-requests.approve');
                Route::post('wnioski-o-konto/{accountRequest}/odrzuc', [AdminAccountRequestController::class, 'reject'])
                    ->name('account-requests.reject');
                Route::post('wnioski-o-konto/{accountRequest}/wyslij-link-hasla', [AdminAccountRequestController::class, 'resendPasswordSetupLink'])
                    ->name('account-requests.password.resend');
                Route::post('uzytkownicy/{user}/wyslij-link-hasla', [UserController::class, 'resendPasswordSetupLink'])
                    ->name('users.password.resend');
                Route::get('uzytkownicy/{user}/dane-czlonkowskie', [MemberProfileController::class, 'edit'])
                    ->name('member-profiles.edit');
                Route::put('uzytkownicy/{user}/dane-czlonkowskie', [MemberProfileController::class, 'update'])
                    ->name('member-profiles.update');

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
