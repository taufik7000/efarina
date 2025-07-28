<?php
// app/Providers/Filament/DirekturPanelProvider.php

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

class DirekturPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('direktur')
            ->path('direktur')
            ->BrandLogo('/storage/assets/logo-efarina.webp')
            ->BrandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Direktur/Resources'), for: 'App\\Filament\\Direktur\\Resources')
            ->viteTheme('resources/css/filament/direktur/theme.css')
            ->resources([
                // Shared Budget Resources untuk Direktur
                \App\Filament\Resources\BudgetPeriodResource::class,
                \App\Filament\Resources\BudgetPlanResource::class,
                \App\Filament\Resources\BudgetCategoryResource::class,
                \App\Filament\Resources\BudgetSubcategoryResource::class,
                \App\Filament\Resources\TransaksiResource::class,
                \App\Filament\Team\Resources\ProjectResource::class,
                \App\Filament\Team\Resources\PengajuanAnggaranResource::class,
                \App\Filament\Team\Resources\TaskResource::class,

            ])
            ->pages([
                \App\Filament\Hrd\Pages\LaporanKehadiran::class,
            ])
            ->discoverPages(in: app_path('Filament/Direktur/Pages'), for: 'App\\Filament\\Direktur\\Pages')
            ->discoverWidgets(in: app_path('Filament/Direktur/Widgets'), for: 'App\\Filament\\Direktur\\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}