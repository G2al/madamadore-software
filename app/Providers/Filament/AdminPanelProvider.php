<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Facades\Filament;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->plugins([
                FilamentFullCalendarPlugin::make(),
            ])
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.scripts.collapse-navigation')
            )
            ->colors([
                'primary' => Color::hex('#7678ED'),
                'gray'    => Color::hex('#273043'),
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\DressesOverview::class,
                \App\Filament\Widgets\DressesEconomics::class,
                \App\Filament\Widgets\DressesCalendar::class,
                \App\Filament\Widgets\FabricSummaryWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        Filament::serving(function () {
            if (request()->routeIs('filament.admin.pages.dashboard')) {
                $upcomingDeliveries = \App\Models\Dress::whereBetween('delivery_date', [
                    now(),
                    now()->addDays(3)
                ])->get();

                if ($upcomingDeliveries->isNotEmpty()) {
                    \Filament\Notifications\Notification::make()
                        ->title('Consegne in Scadenza')
                        ->body("Hai {$upcomingDeliveries->count()} abiti da consegnare nei prossimi 3 giorni")
                        ->warning()
                        ->persistent()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('Visualizza')
                                ->url(route('filament.admin.resources.dresses.index', ['tableFilters' => ['upcoming_deliveries' => ['value' => true]]]))
                        ])
                        ->send();
                }
            }
        });
    }
}