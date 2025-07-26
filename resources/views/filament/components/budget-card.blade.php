<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-bold 
                @if($color === 'blue') text-blue-600 dark:text-blue-400
                @elseif($color === 'red') text-red-600 dark:text-red-400  
                @elseif($color === 'green') text-green-600 dark:text-green-400
                @endif">
                Rp {{ number_format($amount, 0, ',', '.') }}
            </p>
        </div>
        <div class="p-2 rounded-full 
            @if($color === 'blue') bg-blue-100 dark:bg-blue-900/20
            @elseif($color === 'red') bg-red-100 dark:bg-red-900/20
            @elseif($color === 'green') bg-green-100 dark:bg-green-900/20
            @endif">
            <x-heroicon-o-banknotes class="w-6 h-6 
                @if($color === 'blue') text-blue-600 dark:text-blue-400
                @elseif($color === 'red') text-red-600 dark:text-red-400
                @elseif($color === 'green') text-green-600 dark:text-green-400
                @endif" />
        </div>
    </div>
</div>