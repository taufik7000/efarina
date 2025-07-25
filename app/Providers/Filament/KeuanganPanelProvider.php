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

class KeuanganPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('keuangan')
            ->path('keuangan')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/keuangan/theme.css')
            ->discoverResources(in: app_path('Filament/Keuangan/Resources'), for: 'App\\Filament\\Keuangan\\Resources')
            ->resources([
                // Shared Budget Resources untuk Direktur
                \App\Filament\Resources\BudgetPeriodResource::class,
                \App\Filament\Resources\BudgetPlanResource::class,
                \App\Filament\Resources\BudgetCategoryResource::class,
                \App\Filament\Resources\BudgetSubcategoryResource::class,
                \App\Filament\Resources\BudgetAllocationResource::class,
                \App\Filament\Resources\TransaksiResource::class,
                \App\Filament\Team\Resources\PengajuanAnggaranResource::class,
                LeaveRequestResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Keuangan/Pages'), for: 'App\\Filament\\Keuangan\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Keuangan/Widgets'), for: 'App\\Filament\\Keuangan\\Widgets')
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
                'role:keuangan',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
