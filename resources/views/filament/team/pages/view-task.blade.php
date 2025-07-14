{{-- resources/views/filament/team/pages/view-task.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Task Details Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
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
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $record->progress_percentage }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Comments Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Comments ({{ $record->comments->count() }})</h3>
                <button type="button" 
                        onclick="document.getElementById('comment-form').style.display = document.getElementById('comment-form').style.display === 'none' ? 'block' : 'none'"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Add Comment
                </button>
            </div>

            {{-- Add Comment Form - Gunakan Livewire --}}
            <div id="comment-form" style="display: none;" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="mb-4">
                    <label for="newComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comment</label>
                    <textarea 
                        wire:model="newComment" 
                        id="newComment" 
                        rows="3" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white" 
                        placeholder="Write your comment here..."
                        required></textarea>
                    @error('newComment') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="document.getElementById('comment-form').style.display = 'none'"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" 
                            wire:click="addCommentFromBlade"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Add Comment
                    </button>
                </div>
            </div>

            {{-- Comments List --}}
            <div class="space-y-4">
                @forelse($record->comments as $comment)
                    <div class="border-l-4 border-blue-200 pl-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                {{ substr($comment->user->name, 0, 1) }}
                            </div>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $comment->user->name }}</span>
                            <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($comment->comment)) !!}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No comments yet. Be the first to add one!</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>