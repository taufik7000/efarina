{{-- resources/views/filament/team/components/task-progress-updates.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="space-y-4">
    @forelse($getRecord()->progressUpdates as $progress)
        <div class="relative">
            {{-- Timeline Line --}}
            @if(!$loop->last)
                <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-600"></div>
            @endif
            
            <div class="flex items-start space-x-4">
                {{-- Timeline Dot --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 
                            @if($progress->status_change)
                                {{ match($progress->status_change) {
                                    'todo' => 'bg-gray-500',
                                    'in_progress' => 'bg-blue-500',
                                    'review' => 'bg-yellow-500',
                                    'done' => 'bg-green-500',
                                    'blocked' => 'bg-red-500',
                                    default => 'bg-gray-500'
                                } }}
                            @else
                                bg-indigo-500
                            @endif
                            text-white">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>

                {{-- Progress Content --}}
                <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $progress->user->name }}</span>
                            @if($progress->status_change)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                           {{ match($progress->status_change) {
                                               'todo' => 'bg-gray-100 text-gray-800',
                                               'in_progress' => 'bg-blue-100 text-blue-800',
                                               'review' => 'bg-yellow-100 text-yellow-800',
                                               'done' => 'bg-green-100 text-green-800',
                                               'blocked' => 'bg-red-100 text-red-800',
                                               default => 'bg-gray-100 text-gray-800'
                                           } }}">
                                    Status: {{ ucfirst(str_replace('_', ' ', $progress->status_change)) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $progress->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>

                    {{-- Progress Note --}}
                    <div class="text-gray-700 dark:text-gray-300 mb-3">
                        {!! nl2br(e($progress->progress_note)) !!}
                    </div>

                    {{-- Progress Details --}}
                    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                        @if($progress->progress_percentage !== null)
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Progress: {{ $progress->progress_percentage }}%</span>
                            </div>
                        @endif

                        @if($progress->hours_worked)
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $progress->hours_worked }} jam</span>
                            </div>
                        @endif
                    </div>

                    {{-- Attachments --}}
                    @if($progress->attachments && count($progress->attachments) > 0)
                        <div class="mt-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Attachments:</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($progress->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded hover:bg-gray-200 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ basename($attachment) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
            <p>No progress updates yet.</p>
            <p class="text-sm">Start working on this task to track progress!</p>
        </div>
    @endforelse
</div>