<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        {{ $this->form }}

        {{-- Widget Grid --}}
        <div class="grid gap-6" wire:key="widgets-grid">
            {{-- Always show Financial Overview jika dipilih --}}
            @if($this->isWidgetSelected('financial_overview'))
                <div class="col-span-full">
                    @livewire(\App\Filament\Direktur\Widgets\FinancialOverviewWidget::class, [
                        'filters' => $this->getFilters()
                    ], key('financial-overview-' . md5(json_encode($this->getFilters()))))
                </div>
            @endif

            {{-- Row untuk Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($this->isWidgetSelected('budget_breakdown'))
                    <div>
                        @livewire(\App\Filament\Direktur\Widgets\BudgetBreakdownChart::class, [
                            'filters' => $this->getFilters()
                        ], key('budget-breakdown-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('cash_flow_trend'))
                    <div>
                        @livewire(\App\Filament\Direktur\Widgets\CashFlowTrendChart::class, [
                            'filters' => $this->getFilters()
                        ], key('cash-flow-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif
            </div>

            {{-- Row untuk Alerts dan Lists --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @if($this->isWidgetSelected('budget_alerts'))
                    <div>
                        @livewire(\App\Filament\Direktur\Widgets\BudgetAlertsWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('budget-alerts-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('recent_approvals'))
                    <div>
                        @livewire(\App\Filament\Direktur\Widgets\RecentApprovalsWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('recent-approvals-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif

                @if($this->isWidgetSelected('top_spending'))
                    <div>
                        @livewire(\App\Filament\Direktur\Widgets\TopSpendingWidget::class, [
                            'filters' => $this->getFilters()
                        ], key('top-spending-' . md5(json_encode($this->getFilters()))))
                    </div>
                @endif
            </div>

            {{-- Empty State jika tidak ada widget --}}
            @if(empty($this->getSelectedWidgets()))
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <x-heroicon-o-squares-plus class="h-12 w-12 text-gray-400 mx-auto mb-4"/>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Tidak ada widget yang dipilih
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Klik tombol "Kustomisasi Dashboard" untuk memilih widget yang ingin ditampilkan.
                        </p>
                        <x-filament::button 
                            wire:click="mountAction('customize_dashboard')"
                            color="primary">
                            Pilih Widget
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- JavaScript untuk handle real-time updates --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('widgets-updated', () => {
                window.location.reload();
            });
            
            Livewire.on('filters-updated', (event) => {
                // Refresh all widgets dengan filter baru
                Livewire.dispatch('refresh-widgets', event.filters);
            });
        });
    </script>
</x-filament-panels::page>
