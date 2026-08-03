<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\AccountRequest;
use App\Models\SaleListing;
use App\Policies\SaleListingPolicy;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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

        Model::preventLazyLoading(! $this->app->isProduction());

        Paginator::defaultView('components.pagination');

        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();
            $pendingAccountRequestsCount = $user?->role === UserRole::Admin
                ? AccountRequest::query()->pending()->count()
                : 0;

            $view->with('pendingAccountRequestsCount', $pendingAccountRequestsCount);
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
