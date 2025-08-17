<?php

namespace App\Providers;

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
        // Force HTTPS for URL generation if APP_URL uses https
        // This ensures assets are loaded over HTTPS
        if (str_starts_with(config('app.url'), 'https://')) {
            \URL::forceScheme('https');
        }
    }
}
