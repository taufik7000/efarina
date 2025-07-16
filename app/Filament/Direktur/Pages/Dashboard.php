<?php

namespace App\Filament\Direktur\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Actions;

class Dashboard extends BaseDashboard implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.direktur.pages.dashboard';
    
    // Form data untuk filters
    public ?array $data = [];
    
    // State untuk menyimpan widget yang dipilih
    public array $selectedWidgets = [];
    public array $availableWidgets = [];
    public $currentView = 'executive';

    public function mount(): void
    {
        // Load view preference dari session
        $this->currentView = Session::get('direktur_dashboard_view', 'executive');
        
        // Load widget berdasarkan view
        $this->loadPreset($this->currentView);

        // Initialize form data
        $this->data = [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'budget_period' => null,
        ];

        $this->availableWidgets = [
            'financial_overview' => [
                'name' => 'Ringkasan Keuangan',
                'description' => 'Overview budget, pemasukan, dan pengeluaran',
                'widget' => \App\Filament\Direktur\Widgets\FinancialOverviewWidget::class,
                'icon' => 'heroicon-o-currency-dollar',
                'category' => 'Keuangan'
            ],
            'budget_breakdown' => [
                'name' => 'Breakdown Budget',
                'description' => 'Chart alokasi budget per kategori',
                'widget' => \App\Filament\Direktur\Widgets\BudgetBreakdownChart::class,
                'icon' => 'heroicon-o-chart-pie',
                'category' => 'Keuangan'
            ],
            'cash_flow_trend' => [
                'name' => 'Trend Cash Flow',
                'description' => 'Grafik pemasukan vs pengeluaran 6 bulan',
                'widget' => \App\Filament\Direktur\Widgets\CashFlowTrendChart::class,
                'icon' => 'heroicon-o-chart-bar',
                'category' => 'Keuangan'
            ],
            'budget_alerts' => [
                'name' => 'Peringatan Budget',
                'description' => 'Alert budget hampir habis dan overdue',
                'widget' => \App\Filament\Direktur\Widgets\BudgetAlertsWidget::class,
                'icon' => 'heroicon-o-exclamation-triangle',
                'category' => 'Monitoring'
            ],
            'recent_approvals' => [
                'name' => 'Perlu Persetujuan',
                'description' => 'List transaksi dan budget plan pending',
                'widget' => \App\Filament\Direktur\Widgets\RecentApprovalsWidget::class,
                'icon' => 'heroicon-o-clock',
                'category' => 'Approval'
            ],
            'top_spending' => [
                'name' => 'Top Spending',
                'description' => 'Kategori dengan pengeluaran tertinggi',
                'widget' => \App\Filament\Direktur\Widgets\TopSpendingWidget::class,
                'icon' => 'heroicon-o-banknotes',
                'category' => 'Keuangan'
            ],
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Schema kosong karena form dipindah ke header actions
            ])
            ->statePath('data');
    }

    public function updatedFilters(): void
    {
        // Dispatch event to refresh widgets dengan filter baru
        $this->dispatch('filters-updated', filters: $this->getFilters());
    }

    // Method untuk mendapatkan widgets yang dipilih
    public function getSelectedWidgets(): array
    {
        return $this->selectedWidgets;
    }

    // Method untuk mendapatkan data filter
    public function getFilters(): array
    {
        return $this->data ?? [];
    }

    // Method untuk check apakah widget dipilih
    public function isWidgetSelected(string $widgetKey): bool
    {
        return in_array($widgetKey, $this->selectedWidgets);
    }

    // Method untuk get current view name
    public function getCurrentViewName(): string
    {
        $viewNames = [
            'executive' => 'Executive View',
            'financial' => 'Financial View', 
            'monitoring' => 'Monitoring View'
        ];
        
        return $viewNames[$this->currentView] ?? 'Executive View';
    }

    // Header Actions dengan filter periode
    protected function getHeaderActions(): array
    {
        return [
            // View Selection
            Actions\ActionGroup::make([
                Actions\Action::make('view_executive')
                    ->label('Executive View')
                    ->icon('heroicon-o-briefcase')
                    ->color($this->currentView === 'executive' ? 'primary' : 'gray')
                    ->action(function () {
                        $this->switchView('executive');
                    }),
                
                Actions\Action::make('view_financial')
                    ->label('Financial View')
                    ->icon('heroicon-o-chart-bar-square')
                    ->color($this->currentView === 'financial' ? 'primary' : 'gray')
                    ->action(function () {
                        $this->switchView('financial');
                    }),
                
                Actions\Action::make('view_monitoring')
                    ->label('Monitoring View')
                    ->icon('heroicon-o-shield-check')
                    ->color($this->currentView === 'monitoring' ? 'primary' : 'gray')
                    ->action(function () {
                        $this->switchView('monitoring');
                    }),
            ])
            ->label($this->getCurrentViewName())
            ->icon('heroicon-o-squares-2x2')
            ->color('primary')
            ->button(),

            // Filter Periode
            Actions\ActionGroup::make([
                Actions\Action::make('filter_today')
                    ->label('Hari Ini')
                    ->icon('heroicon-o-calendar')
                    ->action(function () {
                        $this->data['date_from'] = now()->toDateString();
                        $this->data['date_to'] = now()->toDateString();
                        $this->form->fill($this->data);
                        $this->updatedFilters();
                    }),
                
                Actions\Action::make('filter_this_week')
                    ->label('Minggu Ini')
                    ->icon('heroicon-o-calendar-days')
                    ->action(function () {
                        $this->data['date_from'] = now()->startOfWeek()->toDateString();
                        $this->data['date_to'] = now()->endOfWeek()->toDateString();
                        $this->form->fill($this->data);
                        $this->updatedFilters();
                    }),
                
                Actions\Action::make('filter_this_month')
                    ->label('Bulan Ini')
                    ->icon('heroicon-o-calendar')
                    ->action(function () {
                        $this->data['date_from'] = now()->startOfMonth()->toDateString();
                        $this->data['date_to'] = now()->endOfMonth()->toDateString();
                        $this->form->fill($this->data);
                        $this->updatedFilters();
                    }),
                
                Actions\Action::make('filter_this_year')
                    ->label('Tahun Ini')
                    ->icon('heroicon-o-calendar')
                    ->action(function () {
                        $this->data['date_from'] = now()->startOfYear()->toDateString();
                        $this->data['date_to'] = now()->endOfYear()->toDateString();
                        $this->form->fill($this->data);
                        $this->updatedFilters();
                    }),
            ])
            ->label('Filter Periode')
            ->icon('heroicon-o-clock')
            ->color('info')
            ->button(),
                
            // Reset Action
            Actions\Action::make('reset_dashboard')
                ->label('Reset')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Dashboard')
                ->modalDescription('Apakah Anda yakin ingin reset dashboard ke pengaturan default?')
                ->action(function () {
                    $this->currentView = 'executive';
                    $this->loadPreset('executive');
                    $this->data = [
                        'date_from' => now()->startOfMonth()->toDateString(),
                        'date_to' => now()->toDateString(),
                        'budget_period' => null,
                    ];
                    Session::put('direktur_dashboard_view', $this->currentView);
                    $this->form->fill($this->data);
                    $this->updatedFilters();
                    $this->dispatch('widgets-updated');
                }),
        ];
    }

    public function switchView(string $view): void
    {
        $this->currentView = $view;
        $this->loadPreset($view);
        Session::put('direktur_dashboard_view', $view);
        $this->dispatch('widgets-updated');
    }

    public function loadPreset(string $preset): void
    {
        $presets = [
            'executive' => ['financial_overview', 'budget_alerts', 'recent_approvals'],
            'financial' => ['financial_overview', 'budget_breakdown', 'cash_flow_trend', 'top_spending'],
            'monitoring' => ['budget_alerts', 'recent_approvals', 'top_spending'],
        ];

        $this->selectedWidgets = $presets[$preset] ?? $presets['executive'];
    }
}