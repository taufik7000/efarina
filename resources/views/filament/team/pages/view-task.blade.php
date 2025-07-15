{{-- resources/views/filament/team/pages/view-task.blade.php --}}
<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Left Column: Task & Todo Details --}}
        {{-- DIUBAH: dari lg:col-span-3 menjadi lg:col-span-2 --}}
        <div class="lg:col-span-2 space-y-6"> 
            {{-- Task Details Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $record->nama_task }}</h2>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Project:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $record->project->nama_project }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                       {{ match($record->status) {
                                           'todo' => 'bg-gray-100 text-gray-800',
                                           'in_progress' => 'bg-blue-100 text-blue-800',
                                           'review' => 'bg-yellow-100 text-yellow-800',
                                           'done' => 'bg-green-100 text-green-800',
                                           'blocked' => 'bg-red-100 text-red-800',
                                           default => 'bg-gray-100 text-gray-800'
                                       } }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Priority:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                       {{ match($record->prioritas) {
                                           'low' => 'bg-gray-100 text-gray-700',
                                           'medium' => 'bg-blue-100 text-blue-700',
                                           'high' => 'bg-orange-100 text-orange-700',
                                           'urgent' => 'bg-red-100 text-red-700',
                                           default => 'bg-gray-100 text-gray-700'
                                       } }}">
                                    {{ ucfirst($record->prioritas) }}
                                </span>
                            </div>
                            @if($record->deskripsi)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Description:</span>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->deskripsi }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="space-y-3">
                            @if($record->assignedTo)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Assigned To:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $record->assignedTo->name }}</span>
                                </div>
                            @endif
                            @if($record->tanggal_deadline)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Deadline:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $record->tanggal_deadline->format('d M Y') }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-sm font-medium text-gray-500">Progress:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $record->progress_percentage }}%</span>
                            </div>
                            @if($record->progress_percentage > 0)
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $record->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Todo Items Section - Only show if feature is available --}}
            @if($this->hasTodoFeature())
                @php
                    $todoItems = $record->todo_items ?? [];
                    $todoStats = $record->todo_stats;
                    $canEdit = $this->canEditTodos();
                @endphp

                @if(!empty($todoItems) || $canEdit)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Todo Items
                                    @if($todoStats['total'] > 0)
                                        <span class="text-sm text-gray-500">({{ $todoStats['completed'] }}/{{ $todoStats['total'] }} completed)</span>
                                    @endif
                                </h3>
                                @if($todoStats['total'] > 0)
                                    <div class="flex items-center mt-2 space-x-2">
                                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all duration-300"
                                                 style="width: {{ $todoStats['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $todoStats['percentage'] }}%</span>
                                    </div>
                                @endif
                            </div>

                            @if($canEdit)
                                <button type="button"
                                        onclick="document.getElementById('todo-form').style.display = document.getElementById('todo-form').style.display === 'none' ? 'block' : 'none'"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    <x-heroicon-o-plus-circle class="w-4 h-4 mr-1"/>
                                    Add Todo
                                </button>
                            @endif
                        </div>

                        {{-- Add Todo Form --}}
                        @if($canEdit)
                            <div id="todo-form" style="display: none;" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="mb-4">
                                    <label for="newTodoItem" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Todo Item</label>
                                    <input type="text"
                                           wire:model="newTodoItem"
                                           id="newTodoItem"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                           placeholder="Enter todo item..."
                                           maxlength="255"
                                           required>
                                    @error('newTodoItem')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="flex justify-end space-x-2">
                                    <button type="button"
                                            onclick="document.getElementById('todo-form').style.display = 'none'"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="button"
                                            wire:click="addTodoItemFromBlade"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        Add Todo
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Todo Items List --}}
                        <div class="space-y-3">
                            @forelse($todoItems as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg {{ $item['completed'] ? 'opacity-75' : '' }}">
                                    <div class="flex items-center space-x-3">
                                        @if($canEdit)
                                            <button type="button"
                                                    wire:click="toggleTodoItem({{ $item['id'] }})"
                                                    class="flex-shrink-0 w-5 h-5 rounded border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center
                                                       {{ $item['completed'] ? 'bg-green-500 border-green-500' : 'hover:border-green-500' }}
                                                       transition-colors duration-200">
                                                @if($item['completed'])
                                                    <x-heroicon-s-check class="w-3 h-3 text-white"/>
                                                @endif
                                            </button>
                                        @else
                                            <div class="flex-shrink-0 w-5 h-5 rounded border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center
                                                    {{ $item['completed'] ? 'bg-green-500 border-green-500' : '' }}">
                                                @if($item['completed'])
                                                    <x-heroicon-s-check class="w-3 h-3 text-white"/>
                                                @endif
                                            </div>
                                        @endif

                                        <span class="text-gray-900 dark:text-gray-100 {{ $item['completed'] ? 'line-through text-gray-500' : '' }}">
                                            {{ $item['text'] }}
                                        </span>

                                        @if($item['completed'])
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                ✓ {{ \Carbon\Carbon::parse($item['completed_at'])->format('d M Y, H:i') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($canEdit)
                                        <button type="button"
                                                wire:click="removeTodoItem({{ $item['id'] }})"
                                                class="text-red-500 hover:text-red-700 p-1">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400"/>
                                    </div>
                                    <p>No todo items yet.</p>
                                    @if($canEdit)
                                        <p class="text-sm">Click "Add Todo" to create your first todo item!</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right Column: Comments --}}
        {{-- DIUBAH: dari lg:col-span-2 menjadi lg:col-span-3 --}}
        <div class="lg:col-span-3"> 
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-xl">
                                <x-heroicon-o-chat-bubble-left class="w-5 h-5 text-blue-600 dark:text-blue-400"/>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Discussion</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->comments->count() }} {{ Str::plural('comment', $record->comments->count()) }}
                                </p>
                            </div>
                            </div>
                        @if($this->canAddComment())
                        <button type="button"
                                onclick="toggleCommentForm()"
                                class="group inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 shadow-lg">
                            <x-heroicon-o-plus class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-200"/>
                            Add Comment
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Add Comment Form --}}
                @if($this->canAddComment())
                <div id="comment-form" style="display: none;" class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                    <div class="space-y-4">
                        <div>
                            <label for="newComment" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Share your thoughts
                            </label>
                            <div class="relative">
                                <textarea
                                    wire:model="newComment"
                                    id="newComment"
                                    rows="4"
                                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white resize-none transition-all duration-200"
                                    placeholder="Write your comment here... You can share ideas, feedback, or ask questions."
                                    required></textarea>

                                {{-- Character counter --}}
                                <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                                    <span x-data="{ length: 0 }" x-text="$wire.newComment ? $wire.newComment.length : 0"></span>/1000
                                </div>
                            </div>
                            @error('newComment')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <x-heroicon-s-exclamation-circle class="w-4 h-4 mr-1"/>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button"
                                    onclick="toggleCommentForm()"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-500 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <x-heroicon-o-x-mark class="w-4 h-4 mr-2"/>
                                Cancel
                            </button>
                            <button type="button"
                                    wire:click="addCommentFromBlade"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 shadow-lg">
                                <x-heroicon-o-paper-airplane class="w-4 h-4 mr-2"/>
                                Post Comment
                            </button>
                        </div>
                    </div>
                </div>
                 @endif
                {{-- Comments List --}}
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($record->comments as $comment)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200 group">
                            <div class="flex space-x-4">
                                {{-- Avatar --}}
                                <div class="flex-shrink-0">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm ring-2 ring-white dark:ring-gray-800 shadow-lg">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>

                                        {{-- Online indicator --}}
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                    </div>
                                </div>

                                {{-- Comment Content --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Header --}}
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $comment->user->name }}
                                            </h4>

                                            {{-- User role badge --}}
                                            @if($comment->user->id === $record->project->project_manager_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">
                                                    <x-heroicon-s-star class="w-3 h-3 mr-1"/>
                                                    Project Manager
                                                </span>
                                            @elseif($comment->user->id === $record->assigned_to)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                                    <x-heroicon-s-user class="w-3 h-3 mr-1"/>
                                                    Assignee
                                                </span>
                                            @endif

                                            {{-- Timestamp --}}
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <div class="flex items-center space-x-2">
                                                <button class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
                                                        title="React to comment">
                                                    <x-heroicon-o-heart class="w-4 h-4 text-gray-400 hover:text-red-500"/>
                                                </button>
                                                <button class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
                                                        title="Reply to comment">
                                                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4 text-gray-400 hover:text-blue-500"/>
                                                </button>
                                                @if($comment->user->id === auth()->id())
                                                    <button class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
                                                            title="Edit comment">
                                                        <x-heroicon-o-pencil class="w-4 h-4 text-gray-400 hover:text-yellow-500"/>
                                                    </button>
                                                    <button class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
                                                            title="Delete comment">
                                                        <x-heroicon-o-trash class="w-4 h-4 text-gray-400 hover:text-red-500"/>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Comment Body --}}
                                    <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-3">
                                        <div class="whitespace-pre-wrap break-words">{{ $comment->comment }}</div>
                                    </div>

                                    {{-- Comment Footer --}}
                                    <div class="flex items-center space-x-6 text-sm text-gray-500 dark:text-gray-400">
                                        <button class="flex items-center space-x-1 hover:text-red-500 transition-colors duration-200">
                                            <x-heroicon-o-heart class="w-4 h-4"/>
                                            <span>Like</span>
                                        </button>
                                        <button class="flex items-center space-x-1 hover:text-blue-500 transition-colors duration-200">
                                            <x-heroicon-o-chat-bubble-left class="w-4 h-4"/>
                                            <span>Reply</span>
                                        </button>
                                        <span class="text-xs">
                                            {{ $comment->created_at->format('M d, Y • g:i A') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-full flex items-center justify-center">
                                <x-heroicon-o-chat-bubble-left class="w-12 h-12 text-blue-400"/>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">No comments yet</h3>
                            <p class="text-sm mb-6 max-w-md mx-auto">
                                Start the conversation! Share your thoughts, ask questions, or provide feedback on this task.
                            </p>
                            <button type="button"
                                    onclick="toggleCommentForm()"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 shadow-lg">
                                <x-heroicon-o-plus-circle class="w-5 h-5 mr-2"/>
                                Add First Comment
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


    {{-- JavaScript for enhanced interactions --}}
    <script>
        function toggleCommentForm() {
            const form = document.getElementById('comment-form');
            const isVisible = form.style.display !== 'none';

            if (isVisible) {
                // Hide with animation
                form.style.opacity = '0';
                form.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    form.style.display = 'none';
                }, 200);
            } else {
                // Show with animation
                form.style.display = 'block';
                form.style.opacity = '0';
                form.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    form.style.opacity = '1';
                    form.style.transform = 'translateY(0)';
                    document.getElementById('newComment').focus();
                }, 10);
            }
        }

        // Auto-resize textarea
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('newComment');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 200) + 'px';
                });
            }
        });

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('comment-form');
            if (form) {
                form.style.transition = 'all 0.2s ease-in-out';
            }
        });
    </script>

    {{-- Enhanced CSS for better animations --}}
    <style>
        .prose p {
            margin-bottom: 0.5rem;
        }

        .prose p:last-child {
            margin-bottom: 0;
        }

        /* Custom scrollbar for textarea */
        textarea::-webkit-scrollbar {
            width: 6px;
        }

        textarea::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        textarea::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        textarea::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark textarea::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark textarea::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark textarea::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Smooth hover effects */
        .group:hover .group-hover\:opacity-100 {
            opacity: 1;
        }

        /* Animation for new comments */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in-up {
            animation: slideInUp 0.3s ease-out;
        }

        /* Line through effect */
        .line-through {
            text-decoration: line-through;
        }

        /* Progress bar animation */
        .transition-all {
            transition: all 0.3s ease;
        }

        /* Hover effects */
        button:hover {
            transform: translateY(-1px);
        }

        /* Smooth checkbox animation */
        input[type="checkbox"] {
            transition: all 0.2s ease;
        }
    </style>
</x-filament-panels::page>