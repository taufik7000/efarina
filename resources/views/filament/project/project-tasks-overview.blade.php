<div class="space-y-6">
    {{-- Tasks Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @php
            $tasks = $getRecord()->tasks;
            $taskStats = [
                'todo' => $tasks->where('status', 'todo')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'review' => $tasks->where('status', 'review')->count(),
                'done' => $tasks->where('status', 'done')->count(),
                'blocked' => $tasks->where('status', 'blocked')->count(),
            ];
            $totalTasks = $tasks->count();
        @endphp

        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $taskStats['todo'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">To Do</div>
        </div>

        <div class="bg-blue-100 dark:bg-blue-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ $taskStats['in_progress'] }}</div>
            <div class="text-sm text-blue-500 dark:text-blue-400">In Progress</div>
        </div>

        <div class="bg-yellow-100 dark:bg-yellow-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-300">{{ $taskStats['review'] }}</div>
            <div class="text-sm text-yellow-500 dark:text-yellow-400">Review</div>
        </div>

        <div class="bg-green-100 dark:bg-green-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-300">{{ $taskStats['done'] }}</div>
            <div class="text-sm text-green-500 dark:text-green-400">Done</div>
        </div>

        <div class="bg-red-100 dark:bg-red-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-300">{{ $taskStats['blocked'] }}</div>
            <div class="text-sm text-red-500 dark:text-red-400">Blocked</div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($totalTasks > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $getRecord()->progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ $getRecord()->progress_percentage }}%"></div>
            </div>
        </div>
    @endif

    {{-- Recent Tasks --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Tasks</h3>
        </div>
        
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($tasks->take(5) as $task)
                <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $task->nama_task }}
                                </h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                           {{ match($task->status) {
                                               'todo' => 'bg-gray-100 text-gray-800',
                                               'in_progress' => 'bg-blue-100 text-blue-800',
                                               'review' => 'bg-yellow-100 text-yellow-800',
                                               'done' => 'bg-green-100 text-green-800',
                                               'blocked' => 'bg-red-100 text-red-800',
                                               default => 'bg-gray-100 text-gray-800'
                                           } }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                @if($task->prioritas === 'urgent' || $task->prioritas === 'high')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                               {{ $task->prioritas === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ ucfirst($task->prioritas) }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($task->assignedTo)
                                    <span>Assigned to: {{ $task->assignedTo->name }}</span>
                                @endif
                                
                                @if($task->tanggal_deadline)
                                    <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                        Due: {{ $task->tanggal_deadline->format('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Progress bar for individual task --}}
                            @if($task->progress_percentage > 0)
                                <div class="mt-2">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs text-gray-500">Progress</span>
                                        <span class="text-xs text-gray-500">{{ $task->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="bg-primary-500 h-1.5 rounded-full" 
                                             style="width: {{ $task->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 ml-4">
                            @if($task->comments_count > 0)
                                <span class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $task->comments_count }}
                                </span>
                            @endif

                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                               class="text-amber-600 hover:text-amber-800 text-sm font-medium">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <p>No tasks created yet.</p>
                    <p class="text-sm">Start by creating your first task!</p>
                </div>
            @endforelse
        </div>

        @if($tasks->count() > 5)
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('index', ['tableFilters[project_id][value]' => $getRecord()->id]) }}" 
                   class="text-sm text-amber-600 hover:text-amber-800 font-medium">
                    View all {{ $tasks->count() }} tasks →
                </a>
            </div>
        @endif
    </div>
</div>