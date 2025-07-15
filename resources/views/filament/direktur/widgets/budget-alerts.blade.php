<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-500"/>
                Peringatan Budget
            </div>
        </x-slot>

        <div class="space-y-4">
            @if($highUsageBudgets->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        🔴 Budget Hampir Habis (>90%)
                    </h4>
                    @foreach($highUsageBudgets as $budget)
                        <div class="flex justify-between items-center p-2 bg-red-50 dark:bg-red-900/20 rounded mb-1">
                            <span class="text-xs">{{ $budget->category->nama_kategori }}</span>
                            <span class="text-xs font-bold text-red-600">
                                {{ number_format($budget->usage_percentage, 1) }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($pendingBudgetPlans->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ⏳ Budget Plans Perlu Approval
                    </h4>
                    @foreach($pendingBudgetPlans as $plan)
                        <div class="flex justify-between items-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded mb-1">
                            <span class="text-xs">{{ $plan->nama_budget }}</span>
                            <span class="text-xs text-yellow-600">
                                {{ $plan->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($overdueTransactions->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        🚨 Transaksi Terlambat Diproses
                    </h4>
                    @foreach($overdueTransactions as $transaction)
                        <div class="flex justify-between items-center p-2 bg-orange-50 dark:bg-orange-900/20 rounded mb-1">
                            <span class="text-xs">{{ $transaction->nama_transaksi }}</span>
                            <span class="text-xs text-orange-600">
                                {{ $transaction->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($highUsageBudgets->count() == 0 && $pendingBudgetPlans->count() == 0 && $overdueTransactions->count() == 0)
                <div class="text-center py-4">
                    <x-heroicon-o-check-circle class="h-8 w-8 text-green-500 mx-auto mb-2"/>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada peringatan saat ini</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>