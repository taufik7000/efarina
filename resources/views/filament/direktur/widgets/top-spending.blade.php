<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chart-bar class="h-5 w-5 text-primary-500"/>
                Top Spending Bulan Ini
            </div>
        </x-slot>

        <div class="space-y-3">
            @forelse($topSpending as $spending)
                @php
                    $categoryName = $spending->budgetAllocation->category->nama_kategori ?? 'Unknown';
                    $amount = number_format($spending->total_spent, 0, ',', '.');
                @endphp
                
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $categoryName }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-primary-600 h-2 rounded-full" 
                                 style="width: {{ min(($spending->total_spent / $topSpending->first()->total_spent) * 100, 100) }}%">
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            Rp {{ $amount }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <x-heroicon-o-banknotes class="h-8 w-8 text-gray-400 mx-auto mb-2"/>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Belum ada pengeluaran bulan ini</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>