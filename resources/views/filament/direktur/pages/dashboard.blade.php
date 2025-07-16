<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Dashboard Header Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-primary-500" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            @switch($this->currentView)
                                @case('executive')
                                    Executive View
                                    @break
                                @case('financial') 
                                    Financial View
                                    @break
                                @case('monitoring')
                                    Monitoring View
                                    @break
                                @default
                                    Executive View
                            @endswitch
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-2 text-sm">
                        @if($this->data['date_from'] && $this->data['date_to'])
                            <span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 rounded-md">
                                {{ \Carbon\Carbon::parse($this->data['date_from'])->format('d M Y') }} - 
                                {{ \Carbon\Carbon::parse($this->data['date_to'])->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        {{ count($this->getSelectedWidgets()) }} widget aktif
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        @switch($this->currentView)
                            @case('executive')
                                <x-heroicon-o-briefcase class="w-4 h-4 text-primary-500"/>
                                @break
                            @case('financial')
                                <x-heroicon-o-chart-bar-square class="w-4 h-4 text-primary-500"/>
                                @break
                            @case('monitoring')
                                <x-heroicon-o-shield-check class="w-4 h-4 text-primary-500"/>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>

        {{-- Widget Grid --}}
        <div class="grid gap-6" wire:key="widgets-grid">
            {{-- Financial Overview Widget (Full Width) --}}
            @if($this->isWidgetSelected('financial_overview'))
                <div class="col-span-full">
                    @livewire(\App\Filament\Direktur\Widgets\FinancialOverviewWidget::class, [
                        'filters' => $this->getFilters()
                    ], key('financial-overview-' . md5(json_encode($this->getFilters()))))
                </div>
            @endif

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($this->isWidgetSelected('budget_breakdown'))
                    <div class="transition-all duration-300 ease-in-out">
                        @livewire(\App\Filament\Direktur\Widgets\BudgetBreakdownChart::class, [
                            'filters' => $this->getFilters()
                        ], key('budget-breakdown-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('cash_flow_trend'))
                    <div class="transition-all duration-300 ease-in-out">
                        @livewire(\App\Filament\Direktur\Widgets\CashFlowTrendChart::class, [
                            'filters' => $this->getFilters()
                        ], key('cash-flow-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif
            </div>

            {{-- Management Widgets Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @if($this->isWidgetSelected('budget_alerts'))
                    <div class="transition-all duration-300 ease-in-out">
                        @livewire(\App\Filament\Direktur\Widgets\BudgetAlertsWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('budget-alerts-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('recent_approvals'))
                    <div class="transition-all duration-300 ease-in-out">
                        @livewire(\App\Filament\Direktur\Widgets\RecentApprovalsWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('recent-approvals-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('top_spending'))
                    <div class="transition-all duration-300 ease-in-out">
                        @livewire(\App\Filament\Direktur\Widgets\TopSpendingWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('top-spending-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif
            </div>

            {{-- Empty State --}}
            @if(empty($this->getSelectedWidgets()))
                <div class="col-span-full">
                    <div class="text-center py-16">
                        <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                            <x-heroicon-o-squares-plus class="w-12 h-12 text-gray-400"/>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Dashboard Kosong
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                            Tidak ada widget yang dipilih untuk ditampilkan. Klik tombol "Kustomisasi Widget" untuk memilih widget yang ingin ditampilkan.
                        </p>
                        <div class="flex justify-center space-x-3">
                            <x-filament::button 
                                wire:click="switchView('executive')"
                                color="primary"
                                size="lg">
                                <x-heroicon-o-briefcase class="w-5 h-5 mr-2"/>
                                Executive View
                            </x-filament::button>
                            <x-filament::button 
                                wire:click="switchView('financial')"
                                color="info"
                                size="lg">
                                <x-heroicon-o-chart-bar-square class="w-5 h-5 mr-2"/>
                                Financial View
                            </x-filament::button>
                            <x-filament::button 
                                wire:click="switchView('monitoring')"
                                color="warning"
                                size="lg">
                                <x-heroicon-o-shield-check class="w-5 h-5 mr-2"/>
                                Monitoring View
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Quick Stats Footer --}}
        @if(!empty($this->getSelectedWidgets()))
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-500"/>
                            <span class="text-gray-600 dark:text-gray-400">
                                Terakhir diperbarui: {{ now()->format('H:i, d M Y') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4 text-gray-500"/>
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ count($this->getSelectedWidgets()) }} dari {{ count($this->availableWidgets) }} widget
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button 
                            wire:click="updatedFilters()"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                            <x-heroicon-o-arrow-path class="w-4 h-4"/>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- JavaScript untuk smooth transitions --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('widgets-updated', () => {
                // Smooth reload untuk widget updates
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            });
            
            Livewire.on('filters-updated', (event) => {
                // Refresh widgets dengan animasi
                const widgets = document.querySelectorAll('[wire\\:key*="widget"]');
                widgets.forEach(widget => {
                    widget.style.opacity = '0.7';
                    setTimeout(() => {
                        widget.style.opacity = '1';
                    }, 500);
                });
            });
        });
    </script>

    {{-- Custom Styles --}}
    <style>
        .transition-all {
            transition: all 0.3s ease-in-out;
        }
        
        .widget-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</x-filament-panels::page>