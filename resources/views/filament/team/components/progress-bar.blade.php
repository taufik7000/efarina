{{-- resources/views/filament/team/components/progress-bar.blade.php --}}
<div class="flex items-center space-x-2">
    <div class="task-progress-bar max-w-16">
        <div class="task-progress-fill {{ match(true) {
                        $getRecord()->progress_percentage >= 100 => 'completed',
                        $getRecord()->progress_percentage >= 75 => 'high',
                        $getRecord()->progress_percentage >= 50 => 'medium',
                        $getRecord()->progress_percentage >= 25 => 'low',
                        default => 'default'
                    } }}" 
             style="width: {{ min($getRecord()->progress_percentage, 100) }}%">
        </div>
    </div>
    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-fit">
        {{ $getRecord()->progress_percentage }}%
    </span>
</div>