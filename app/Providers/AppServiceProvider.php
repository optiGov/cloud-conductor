<?php

namespace App\Providers;

use Filament\Facades\Filament;
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
        // register styles and scripts
        Filament::registerStyles([
            asset('css/app.css'),
        ]);

        Filament::serving(function () {
            // Using Vite
            Filament::registerViteTheme('resources/css/cloud-conductor.css');
        });
    }
}
