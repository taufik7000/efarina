{{-- resources/views/filament/team/components/todo-stats.blade.php --}}
@php
    $stats = $getRecord()->todo_stats;
    $hasItems = $stats['total'] > 0;
@endphp

<div class="flex items-center space-x-2">
    @if($hasItems)
        <div class="flex items-center space-x-1">
            <div class="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded-full p-0.5">
                <div class="w-full h-full bg-green-500 rounded-full" 
                     style="width: {{ $stats['percentage'] }}%"></div>
            </div>
            <span class="text-xs text-gray-600 dark:text-gray-400">
                {{ $stats['completed'] }}/{{ $stats['total'] }}
            </span>
        </div>
        
        @if($stats['percentage'] == 100)
            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
        @endif
    @else
        <span class="text-xs text-gray-400">
            No items
        </span>
    @endif
</div>
