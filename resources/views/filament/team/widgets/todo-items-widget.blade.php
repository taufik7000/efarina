{{-- resources/views/filament/team/widgets/todo-items-widget.blade.php --}}
@php
    $todoTasks = $this->getTodoTasks();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-blue-500"/>
                <span>My Todo Items</span>
                <span class="text-sm text-gray-500">({{ $todoTasks->sum('total_todos') }} items)</span>
            </div>
        </x-slot>

        <div class="space-y-6">
            @forelse($todoTasks as $taskData)
                @php
                    $task = $taskData['task'];
                    $incompleteTodos = $taskData['incomplete_todos'];
                    $totalTodos = $taskData['total_todos'];
                    $completedTodos = $taskData['completed_todos'];
                @endphp

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    {{-- Task Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                                   class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $task->nama_task }}
                                </a>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                           {{ match($task->status) {
                                               'todo' => 'bg-gray-100 text-gray-800',
                                               'in_progress' => 'bg-blue-100 text-blue-800',
                                               'review' => 'bg-yellow-100 text-yellow-800',
                                               'blocked' => 'bg-red-100 text-red-800',
                                               default => 'bg-gray-100 text-gray-800'
                                           } }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span>{{ $task->project->nama_project }}</span>
                                @if($task->tanggal_deadline)
                                    <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                        Due: {{ $task->tanggal_deadline->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $completedTodos }}/{{ $totalTodos }} completed
                            </div>
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Todo Items --}}
                    <div class="space-y-2">
                        @foreach($incompleteTodos as $item)
                            <div class="flex items-center space-x-3 p-2 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                <button type="button" 
                                        wire:click="toggleTodoItem({{ $task->id }}, {{ $item['id'] }})"
                                        class="flex-shrink-0 w-5 h-5 rounded border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center
                                               hover:border-green-500 transition-colors duration-200">
                                    {{-- Checkbox akan kosong karena ini incomplete items --}}
                                </button>
                                
                                <span class="text-gray-900 dark:text-gray-100 flex-1">
                                    {{ $item['text'] }}
                                </span>
                                
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($item['created_at'])->format('M d') }}
                                </span>
                            </div>
                        @endforeach
                        
                        @if($completedTodos > 0)
                            <div class="text-center pt-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $completedTodos }} item{{ $completedTodos > 1 ? 's' : '' }} completed
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-gray-400"/>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No todo items</h3>
                    <p class="text-sm">When you create todo items in your tasks, they'll appear here.</p>
                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('index') }}" 
                       class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <x-heroicon-o-plus-circle class="w-4 h-4 mr-2"/>
                        View My Tasks
                    </a>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>