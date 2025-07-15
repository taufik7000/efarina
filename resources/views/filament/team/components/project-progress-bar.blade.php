{{-- resources/views/filament/team/components/project-progress-bar.blade.php --}}
@php
    $progress = $getRecord()->progress_percentage ?? 0;
    $status = $getRecord()->status;
    
    $progressColor = match(true) {
        $progress >= 100 => 'from-green-500 to-green-600',
        $progress >= 75 => 'from-blue-500 to-blue-600',
        $progress >= 50 => 'from-yellow-500 to-yellow-600',
        $progress >= 25 => 'from-orange-500 to-orange-600',
        default => 'from-gray-400 to-gray-500'
    };
    
    $bgColor = match($status) {
        'completed' => 'bg-green-100 dark:bg-green-900/20',
        'cancelled' => 'bg-red-100 dark:bg-red-900/20',
        'on_hold' => 'bg-yellow-100 dark:bg-yellow-900/20',
        default => 'bg-gray-100 dark:bg-gray-800'
    };
@endphp

<div class="flex items-center space-x-3">
    <div class="flex-1">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Progress</span>
            <span class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $progress }}%</span>
        </div>
        <div class="w-full {{ $bgColor }} rounded-full h-2 overflow-hidden">
            <div class="bg-gradient-to-r {{ $progressColor }} h-2 rounded-full transition-all duration-500 ease-out relative" 
                 style="width: {{ min($progress, 100) }}%">
                @if($progress > 0)
                    <div class="absolute inset-0 bg-white/20 rounded-full animate-pulse"></div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Progress icon --}}
    <div class="flex-shrink-0">
        @if($status === 'completed')
            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" title="Completed"/>
        @elseif($status === 'cancelled')
            <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" title="Cancelled"/>
        @elseif($status === 'on_hold')
            <x-heroicon-s-pause-circle class="w-5 h-5 text-yellow-500" title="On Hold"/>
        @elseif($progress >= 75)
            <x-heroicon-s-fire class="w-5 h-5 text-orange-500" title="Almost Done"/>
        @elseif($progress >= 50)
            <x-heroicon-s-rocket-launch class="w-5 h-5 text-blue-500" title="In Progress"/>
        @elseif($progress >= 25)
            <x-heroicon-s-play class="w-5 h-5 text-indigo-500" title="Started"/>
        @else
            <x-heroicon-s-clock class="w-5 h-5 text-gray-400" title="Not Started"/>
        @endif
    </div>
</div>