<div class="flex items-center space-x-2">
    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-16">
        <div class="h-2 rounded-full transition-all duration-300 
                    {{ match(true) {
                        $getRecord()->progress_percentage >= 100 => 'bg-green-500',
                        $getRecord()->progress_percentage >= 75 => 'bg-blue-500',
                        $getRecord()->progress_percentage >= 50 => 'bg-yellow-500',
                        $getRecord()->progress_percentage >= 25 => 'bg-orange-500',
                        default => 'bg-gray-400'
                    } }}" 
             style="width: {{ min($getRecord()->progress_percentage, 100) }}%">
        </div>
    </div>
    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-8">
        {{ $getRecord()->progress_percentage }}%
    </span>
</div>