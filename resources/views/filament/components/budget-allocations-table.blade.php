{{-- resources/views/filament/components/budget-allocations-table.blade.php --}}

<div class="space-y-4">
    @php
        $allocations = $getState();
        $hasAllocations = $allocations && $allocations->count() > 0;
    @endphp

    @if($hasAllocations)
        <!-- Header dengan statistik -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center space-x-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Alokasi Budget ({{ $allocations->count() }})
                </h4>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Total: Rp {{ number_format($allocations->sum('allocated_amount'), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex space-x-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    📊 {{ $allocations->count() }} Alokasi
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Kategori
                        </th>
                        <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Alokasi
                        </th>
                        <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Terpakai
                        </th>
                        <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Sisa
                        </th>
                        <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            %
                        </th>
                        <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($allocations as $allocation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-3 px-3">
                                <div class="space-y-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                                        {{ $allocation->category->nama_kategori }}
                                    </div>
                                    @if($allocation->subcategory)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $allocation->subcategory->nama_subkategori }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="font-medium text-blue-600 dark:text-blue-400 text-sm">
                                    {{ number_format($allocation->allocated_amount / 1000000, 1) }}M
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Rp {{ number_format($allocation->allocated_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="font-medium text-orange-600 dark:text-orange-400 text-sm">
                                    {{ number_format($allocation->used_amount / 1000000, 1) }}M
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Rp {{ number_format($allocation->used_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="font-medium {{ $allocation->remaining_amount <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} text-sm">
                                    {{ number_format($allocation->remaining_amount / 1000000, 1) }}M
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Rp {{ number_format($allocation->remaining_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @php
                                    $percentage = $allocation->usage_percentage;
                                    $badgeColor = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                    
                                    if ($percentage >= 100) {
                                        $badgeColor = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                    } elseif ($percentage >= 90) {
                                        $badgeColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                                    } elseif ($percentage >= 70) {
                                        $badgeColor = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
                                    }
                                @endphp
                                
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                                
                                {{-- Progress Bar --}}
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1 dark:bg-gray-700">
                                    <div class="h-1.5 rounded-full {{ $percentage >= 90 ? 'bg-red-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    {{-- View Detail --}}
                                    <a href="#" onclick="viewAllocation({{ $allocation->id }})" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                       title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    
                                    {{-- Edit --}}
                                    @if(auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']))
                                        <a href="#" onclick="editAllocation({{ $allocation->id }})" 
                                           class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300 transition-colors"
                                           title="Edit Alokasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    
                                    {{-- Delete --}}
                                    @if(auth()->user()->hasRole(['admin', 'super-admin', 'direktur']))
                                        <button type="button" 
                                                onclick="confirmDelete({{ $allocation->id }})"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                title="Hapus Alokasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        
                        @if($allocation->catatan)
                            <tr>
                                <td colspan="6" class="py-2 px-3 bg-gray-50 dark:bg-gray-900">
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">💬 Catatan:</span> {{ $allocation->catatan }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Compact Summary -->
        <div class="grid grid-cols-2 gap-3 p-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 rounded-lg border">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ $allocations->count() }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-900 dark:text-gray-100">Total Alokasi</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Kategori Budget</div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <span class="text-red-600 dark:text-red-400 font-bold text-sm">{{ $allocations->where('usage_percentage', '>=', 90)->count() }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-900 dark:text-gray-100">Perlu Perhatian</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Penggunaan ≥ 90%</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Terakhir diperbarui: {{ now()->format('d M Y H:i') }}
            </div>
            <div class="flex space-x-2">
                <button type="button" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                    🔄 Refresh
                </button>
                <button type="button" class="text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                    📊 Detail
                </button>
            </div>
        </div>

    @else
        <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Belum ada alokasi budget
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Mulai dengan membuat alokasi budget untuk rencana ini.
                </p>
                <div class="mt-4">
                    <button type="button" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                        💡 Mulai Buat Alokasi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function getBaseUrl() {
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/keuangan/')) {
        return '/keuangan/budget-allocations';
    } else if (currentPath.includes('/direktur/')) {
        return '/direktur/budget-allocations';
    } else {
        return '/admin/budget-allocations';
    }
}

function viewAllocation(allocationId) {
    const baseUrl = getBaseUrl();
    window.location.href = `${baseUrl}/${allocationId}`;
}

function editAllocation(allocationId) {
    const baseUrl = getBaseUrl();
    window.location.href = `${baseUrl}/${allocationId}/edit`;
}

function confirmDelete(allocationId) {
    if (confirm('Apakah Anda yakin ingin menghapus alokasi ini? Tindakan ini tidak dapat dibatalkan.')) {
        const baseUrl = getBaseUrl();
        
        // Create form untuk delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${baseUrl}/${allocationId}`;
        form.style.display = 'none';
        
        // CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Method DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}
</script>