<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

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
        // Registra asset personalizzati per il calendario delle consegne
        FilamentAsset::register([
            Css::make('delivery-calendar-css', resource_path('css/delivery-calendar.css')),
            Js::make('delivery-calendar-js', resource_path('js/delivery-calendar.js')),
        ]);
    }
}
