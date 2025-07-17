{{-- resources/views/filament/team/columns/project-progress.blade.php --}}
@php
    $progress = $getRecord()->progress_percentage ?? 0;
    $status = $getRecord()->status;
    
    // Tentukan warna berdasarkan progress dan status
    $progressColor = match(true) {
        $progress >= 100 => 'bg-green-500',
        $progress >= 80 => 'bg-blue-500',
        $progress >= 50 => 'bg-yellow-500',
        $progress >= 25 => 'bg-orange-500',
        default => 'bg-gray-400'
    };
    
    $textColor = match(true) {
        $progress >= 100 => 'text-green-600',
        $progress >= 80 => 'text-blue-600',
        $progress >= 50 => 'text-yellow-600',
        $progress >= 25 => 'text-orange-600',
        default => 'text-gray-500'
    };
    
    $bgColor = 'bg-gray-200 dark:bg-gray-700';
@endphp

<div class="flex items-center space-x-3 min-w-0">
    {{-- Progress Bar --}}
    <div class="flex-1 min-w-0">
        <div class="w-full {{ $bgColor }} rounded-full h-2 overflow-hidden">
            <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-500 ease-out relative" 
                 style="width: {{ min($progress, 100) }}%">
                @if($progress > 0)
                    <div class="absolute inset-0 bg-white/30 rounded-full"></div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Percentage Text --}}
    <div class="flex-shrink-0">
        <span class="text-sm font-semibold {{ $textColor }}">
            {{ $progress }}%
        </span>
    </div>
</div>