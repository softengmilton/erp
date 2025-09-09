<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Inertia\Inertia;
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
        Inertia::share([
            'toast' => fn() => session()->get('toast'),

            // Lazy-loaded settings (only when rendering an Inertia response)
            'storeSettings' => fn() => StoreSetting::pluck('value', 'key'),
        ]);
    }
}
