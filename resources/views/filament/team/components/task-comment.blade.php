{{-- resources/views/filament/team/components/task-comments.blade.php --}}
<div class="space-y-4">
    @forelse($getRecord()->comments as $comment)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            {{-- Comment Header --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                        {{ substr($comment->user->name, 0, 1) }}
                    </div>
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $comment->user->name }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $comment->created_at->format('d M Y, H:i') }}
                </div>
            </div>

            {{-- Comment Content --}}
            <div class="text-gray-700 dark:text-gray-300 mb-3">
                {!! nl2br(e($comment->comment)) !!}
            </div>

            {{-- Attachments --}}
            @if($comment->attachments && count($comment->attachments) > 0)
                <div class="mb-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Attachments:</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($comment->attachments as $attachment)
                            <a href="{{ Storage::url($attachment) }}" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full hover:bg-blue-200 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                                </svg>
                                {{ basename($attachment) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reply Section --}}
            @if($comment->replies && $comment->replies->count() > 0)
                <div class="mt-3 ml-4 space-y-2 border-l-2 border-gray-200 dark:border-gray-600 pl-4">
                    @foreach($comment->replies as $reply)
                        <div class="bg-white dark:bg-gray-700 rounded p-3 text-sm">
                            <div class="flex items-center space-x-2 mb-1">
                                <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                    {{ substr($reply->user->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $reply->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-gray-600 dark:text-gray-300">
                                {!! nl2br(e($reply->comment)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
            </svg>
            <p>No comments yet.</p>
            <p class="text-sm">Be the first to add a comment!</p>
        </div>
    @endforelse
</div>