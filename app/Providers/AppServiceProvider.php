<?php

namespace App\Providers;

use App\Models\Repository;
use Illuminate\Support\Facades\Route;
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

        // Custom route model binding for API routes
        Route::bind('repository', function ($value) {
            // If the current route is an API route and value is numeric, bind by ID
            if (request()->is('api/*') && is_numeric($value)) {
                return Repository::findOrFail($value);
            }
            // Otherwise, use the default behavior (slug)
            return Repository::where('slug', $value)->firstOrFail();
        });
    }
}
