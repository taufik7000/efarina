<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-warning-500"/>
                Perlu Persetujuan Anda
            </div>
        </x-slot>

        <div class="space-y-4">
            @if($pendingTransactions->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        💰 Transaksi Pending
                    </h4>
                    @foreach($pendingTransactions as $transaction)
                        <div class="border-l-4 border-warning-500 pl-3 py-2 bg-warning-50 dark:bg-warning-900/20 rounded">
                            <p class="text-sm font-medium">{{ $transaction->nama_transaksi }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }} • 
                                {{ $transaction->createdBy->name }} • 
                                {{ $transaction->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                    
                    <a href="{{ route('filament.direktur.resources.transaksis.index', ['tableFilters[status][value]' => 'pending']) }}" 
                       class="text-xs text-primary-600 hover:text-primary-800">
                        Lihat semua transaksi pending →
                    </a>
                </div>
            @endif

            @if($pendingBudgetPlans->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        📋 Budget Plans
                    </h4>
                    @foreach($pendingBudgetPlans as $plan)
                        <div class="border-l-4 border-info-500 pl-3 py-2 bg-info-50 dark:bg-info-900/20 rounded">
                            <p class="text-sm font-medium">{{ $plan->nama_budget }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $plan->period->nama_periode }} • 
                                Rp {{ number_format($plan->total_budget, 0, ',', '.') }} • 
                                {{ $plan->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                    
                    <a href="{{ route('filament.direktur.resources.budget-plans.index', ['tableFilters[status][value]' => 'draft']) }}" 
                       class="text-xs text-primary-600 hover:text-primary-800">
                        Lihat semua budget plans →
                    </a>
                </div>
            @endif

            @if($pendingTransactions->count() == 0 && $pendingBudgetPlans->count() == 0)
                <div class="text-center py-4">
                    <x-heroicon-o-check-circle class="h-8 w-8 text-green-500 mx-auto mb-2"/>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada yang perlu disetujui</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>