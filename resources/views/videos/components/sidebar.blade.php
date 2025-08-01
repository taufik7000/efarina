{{-- Video Sidebar Component --}}
<div class="lg:col-span-1 space-y-6">

    {{-- Popular Videos --}}
    @php
    $popularVideos = \App\Models\YoutubeVideo::active()
        ->select(['id', 'video_id', 'title', 'thumbnail_url', 'view_count', 'duration_seconds'])
        ->orderBy('view_count', 'desc')
        ->limit(5)
        ->get();
    @endphp
    @if($popularVideos->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <div class="w-2 h-6 bg-gradient-to-b from-orange-500 to-red-600 rounded-full mr-3"></div>
                Video Populer
            </h3>
        </div>
    <div class="p-2 space-y-2">
        @foreach($popularVideos as $index => $popular)
        {{-- Item Video --}}
        <a href="{{ route('video.show', $popular->video_id) }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-blue-50 transition-colors group">
            
            {{-- Nomor Urut --}}
            <div class="flex-shrink-0 text-xl font-bold text-gray-400 group-hover:text-blue-600 transition-colors w-6 text-center">
                {{ $index + 1 }}
            </div>
            
            {{-- Thumbnail --}}
            <div class="relative flex-shrink-0">
                <img src="{{ $popular->thumbnail_hq }}" 
                     alt="{{ $popular->title }}" 
                     class="w-24 h-14 object-cover rounded-lg shadow-sm"
                     loading="lazy">
                <div class="absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-1.5 py-0.5 rounded">
                    {{ $popular->formatted_duration }}
                </div>
            </div>

            {{-- Info Video --}}
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm text-gray-800 mb-1 leading-snug line-clamp-2 group-hover:text-blue-700 transition-colors">
                    {{ $popular->title }}
                </h4>
                <div class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-eye mr-1.5"></i>
                    <span>{{ $popular->formatted_view_count }} Views</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    </div>
    @endif
</div>