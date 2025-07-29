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
use App\Filament\Resources\LeaveRequestResource;

class RedaksiPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('redaksi')
            ->path('redaksi')
            ->BrandLogo('/storage/assets/logo-efarina.webp')
            ->BrandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/redaksi/theme.css')
            ->discoverResources(in: app_path('Filament/Redaksi/Resources'), for: 'App\\Filament\\Redaksi\\Resources')
             ->resources([
                \App\Filament\Team\Resources\TaskResource::class,
                \App\Filament\Team\Resources\PengajuanAnggaranResource::class,
                \App\Filament\Team\Resources\ProjectResource::class,
                LeaveRequestResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Redaksi/Pages'), for: 'App\\Filament\\Redaksi\\Pages')
            ->discoverWidgets(in: app_path('Filament/Redaksi/Widgets'), for: 'App\\Filament\\Redaksi\\Widgets')
            ->widgets([
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
                'role:redaksi',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
