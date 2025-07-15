<?php

namespace App\Filament\Direktur\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

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

    public function mount(): void
    {
        // Load widget preferences dari session
        $this->selectedWidgets = Session::get('direktur_selected_widgets', [
            'financial_overview',
            'budget_alerts', 
            'recent_approvals'
        ]);

        // Initialize form data
        $this->data = [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'budget_period' => null,
            'widget_selection' => $this->selectedWidgets,
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
                Forms\Components\Section::make('Filter Dashboard')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('date_from')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->updatedFilters()),
                                
                                Forms\Components\DatePicker::make('date_to')
                                    ->label('Sampai Tanggal')
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->updatedFilters()),
                                    
                                Forms\Components\Select::make('budget_period')
                                    ->label('Periode Budget')
                                    ->options(
                                        \App\Models\BudgetPeriod::pluck('nama_periode', 'id')->toArray()
                                    )
                                    ->placeholder('Semua Periode')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->updatedFilters()),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),
                    
                Forms\Components\Section::make('Kustomisasi Widget')
                    ->schema([
                        Forms\Components\CheckboxList::make('widget_selection')
                            ->label('Pilih Widget yang Ditampilkan')
                            ->options(collect($this->availableWidgets)->mapWithKeys(function ($widget, $key) {
                                return [$key => $widget['name'] . ' - ' . $widget['description']];
                            })->toArray())
                            ->columns(2)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedWidgets = $state ?? [];
                                Session::put('direktur_selected_widgets', $this->selectedWidgets);
                                $this->dispatch('widgets-updated');
                            }),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed(),
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

    // Header Actions untuk quick widget management
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('customize_dashboard')
                ->label('Kustomisasi Dashboard')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->form([
                    Forms\Components\CheckboxList::make('widgets')
                        ->label('Pilih Widget')
                        ->options(collect($this->availableWidgets)->groupBy('category')->map(function ($widgets, $category) {
                            return $widgets->mapWithKeys(function ($widget, $key) {
                                return [$key => $widget['name']];
                            });
                        })->toArray())
                        ->default($this->selectedWidgets)
                        ->columns(1),
                ])
                ->fillForm(['widgets' => $this->selectedWidgets])
                ->action(function (array $data) {
                    $this->selectedWidgets = $data['widgets'] ?? [];
                    Session::put('direktur_selected_widgets', $this->selectedWidgets);
                    $this->dispatch('widgets-updated');
                }),
                
            \Filament\Actions\Action::make('load_preset')
                ->label('Preset Dashboard')
                ->icon('heroicon-o-squares-2x2')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('preset')
                        ->label('Pilih Preset')
                        ->options([
                            'executive' => 'Executive - Overview & Approval',
                            'financial' => 'Financial - Detail Keuangan',
                            'monitoring' => 'Monitoring - Alert & Tracking',
                            'full' => 'Full - Semua Widget',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->loadPreset($data['preset']);
                }),
                
            \Filament\Actions\Action::make('reset_dashboard')
                ->label('Reset ke Default')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $this->selectedWidgets = ['financial_overview', 'budget_alerts', 'recent_approvals'];
                    Session::put('direktur_selected_widgets', $this->selectedWidgets);
                    $this->data['widget_selection'] = $this->selectedWidgets;
                    $this->form->fill($this->data);
                    $this->dispatch('widgets-updated');
                }),
        ];
    }

    public function loadPreset(string $preset): void
    {
        $presets = [
            'executive' => ['financial_overview', 'budget_alerts', 'recent_approvals'],
            'financial' => ['financial_overview', 'budget_breakdown', 'cash_flow_trend', 'top_spending'],
            'monitoring' => ['budget_alerts', 'recent_approvals', 'top_spending'],
            'full' => array_keys($this->availableWidgets),
        ];

        $this->selectedWidgets = $presets[$preset] ?? $presets['executive'];
        Session::put('direktur_selected_widgets', $this->selectedWidgets);
        $this->data['widget_selection'] = $this->selectedWidgets;
        $this->form->fill($this->data);
        $this->dispatch('widgets-updated');
    }
}
