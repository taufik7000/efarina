{{-- resources/views/filament/components/budget-transactions-table.blade.php --}}

<div class="space-y-4">
    @php
        $transactions = $getState();
        $hasTransactions = $transactions && $transactions->count() > 0;
    @endphp

    @if($hasTransactions)
        <!-- Header dengan statistik -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center space-x-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Transaksi Terbaru ({{ $transactions->count() }})
                </h4>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Total: Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex space-x-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    📊 {{ $transactions->count() }} Terbaru
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Kategori
                        </th>
                        <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Jumlah
                        </th>
                        <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($transactions as $transaction)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-3 px-3">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($transaction->tanggal_transaksi)->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($transaction->tanggal_transaksi)->format('H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="space-y-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100 text-sm max-w-xs truncate" title="{{ $transaction->deskripsi }}">
                                        {{ $transaction->deskripsi }}
                                    </div>
                                    @if($transaction->createdBy)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $transaction->createdBy->name }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                @if($transaction->budgetAllocation)
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $transaction->budgetAllocation->category->nama_kategori }}
                                        </div>
                                        @if($transaction->budgetAllocation->subcategory)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $transaction->budgetAllocation->subcategory->nama_subkategori }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada alokasi</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="space-y-1">
                                    <div class="font-bold text-red-600 dark:text-red-400 text-sm">
                                        -{{ number_format($transaction->total_amount / 1000000, 1) }}M
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                                    ];
                                    
                                    $statusIcons = [
                                        'pending' => '⏳',
                                        'completed' => '✅',
                                        'cancelled' => '❌',
                                        'draft' => '📝',
                                    ];
                                    
                                    $badgeColor = $statusColors[$transaction->status] ?? $statusColors['draft'];
                                    $icon = $statusIcons[$transaction->status] ?? $statusIcons['draft'];
                                @endphp
                                
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                                    {{ $icon }} {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Summary Footer -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <div class="text-center">
                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                    {{ $transactions->where('status', 'completed')->count() }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Transaksi Selesai
                </div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                    {{ $transactions->where('status', 'pending')->count() }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Pending
                </div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-600 dark:text-gray-400">
                    Rp {{ number_format($transactions->where('status', 'completed')->sum('total_amount') / 1000000, 1) }}M
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Total Terealisasi
                </div>
            </div>
        </div>

        <!-- Pagination Info -->
        <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Menampilkan {{ $transactions->count() }} transaksi terbaru dari budget plan ini
            </div>
            <div class="flex space-x-2">
                <button type="button" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                    📄 Lihat Semua
                </button>
                <button type="button" class="text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                    📊 Export
                </button>
            </div>
        </div>

    @else
        <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Belum ada transaksi
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Transaksi akan muncul di sini setelah budget plan digunakan.
                </p>
                <div class="mt-4">
                    <button type="button" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                        💡 Mulai Buat Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>