<div class="space-y-3">
    @php 
        $auditTrails = $getState();
        // Pastikan $auditTrails tidak null dan merupakan collection
        if (!$auditTrails || !is_iterable($auditTrails)) {
            $auditTrails = collect();
        }
    @endphp
    
    @forelse($auditTrails as $audit)
        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                @switch($audit->action)
                    @case('created')
                        <span class="text-green-600 text-sm">➕</span>
                        @break
                    @case('budget_increased')
                        <span class="text-blue-600 text-sm">💰</span>
                        @break
                    @case('allocation_added')
                        <span class="text-yellow-600 text-sm">📊</span>
                        @break
                    @default
                        <span class="text-gray-600 text-sm">📝</span>
                @endswitch
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $audit->formatted_action ?? ucfirst($audit->action ?? 'Unknown') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $audit->created_at?->diffForHumans() ?? 'Unknown time' }}
                    </p>
                </div>
                
                @if($audit->description ?? false)
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                        {{ $audit->description }}
                    </p>
                @endif
                
                @if($audit->amount_changed ?? false)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $audit->amount_changed > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} mt-1">
                        {{ $audit->formatted_amount ?? 'Rp 0' }}
                    </span>
                @endif
                
                @if($audit->reason ?? false)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                        "{{ $audit->reason }}"
                    </p>
                @endif
                
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    oleh {{ $audit->user_name ?? 'Unknown' }} • {{ $audit->created_at?->format('d M Y H:i') ?? 'Unknown date' }}
                </p>
            </div>
        </div>
    @empty
        <div class="text-center py-8">
            <div class="text-gray-400 text-4xl mb-2">📋</div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada aktivitas audit trail
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                Aktivitas akan muncul setelah ada perubahan budget
            </p>
        </div>
    @endforelse
    
    @if($auditTrails->count() >= 10)
        <div class="text-center mt-4">
            <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua aktivitas →
            </button>
        </div>
    @endif
</div>