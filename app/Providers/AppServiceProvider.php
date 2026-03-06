<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (filter_var(env('FORCE_HTTPS', $this->app->environment('production')), FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);
    }
}
