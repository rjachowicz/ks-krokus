<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\AccountRequest;
use App\Models\MemberProfile;
use App\Models\SaleListing;
use App\Policies\MemberProfilePolicy;
use App\Policies\SaleListingPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make(ConfigRepository::class);
        $pgsql = $config->get('database.connections.pgsql');

        $config->set('database.connections', ['pgsql' => $pgsql]);
    }

    public function boot(): void
    {
        Gate::policy(SaleListing::class, SaleListingPolicy::class);
        Gate::policy(MemberProfile::class, MemberProfilePolicy::class);

        Model::preventLazyLoading(! $this->app->isProduction());

        Paginator::defaultView('components.pagination');

        $this->configureRateLimiters();

        View::composer('partials.header', function ($view): void {
            $user = auth()->user();

            $view->with(
                'unreadNotificationsCount',
                $user?->unreadNotifications()->count() ?? 0,
            );
        });

        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();
            $pendingAccountRequestsCount = $user?->role === UserRole::Admin
                ? AccountRequest::query()->pending()->count()
                : 0;

            $view->with([
                'pendingAccountRequestsCount' => $pendingAccountRequestsCount,
                'unreadNotificationsCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', static fn (Request $request): Limit => Limit::perMinute(6)
            ->by($request->ip()));
        RateLimiter::for('password-reset', static fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip()));
        RateLimiter::for('password-update', static fn (Request $request): Limit => Limit::perMinute(6)
            ->by($request->ip()));
        RateLimiter::for('contact', static fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip()));
        RateLimiter::for('account-requests', static fn (Request $request): Limit => Limit::perHour(3)
            ->by($request->ip()));
        RateLimiter::for('listing-reports', static fn (Request $request): Limit => Limit::perHour(3)
            ->by($request->ip()));
        RateLimiter::for('notification-actions', static fn (Request $request): Limit => Limit::perMinute(30)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('event-reminders', static fn (Request $request): Limit => Limit::perMinute(10)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
