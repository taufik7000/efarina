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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class HrdPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('hrd')
            ->path('hrd')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/hrd/theme.css')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')  
            ->discoverResources(in: app_path('Filament/Hrd/Resources'), for: 'App\\Filament\\Hrd\\Resources')
            ->discoverPages(in: app_path('Filament/Hrd/Pages'), for: 'App\\Filament\\Hrd\\Pages')
            ->discoverWidgets(in: app_path('Filament/Hrd/Widgets'), for: 'App\\Filament\\Hrd\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                'role:hrd',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
