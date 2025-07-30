{{-- Video Sidebar Component --}}
<div class="lg:col-span-1 space-y-6">
    {{-- Categories --}}
    @if($categories->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <div class="w-2 h-6 bg-gradient-to-b from-slate-600 to-slate-800 rounded-full mr-3"></div>
                Kategori Video
            </h3>
        </div>
    </div>
    @endif

    {{-- Sort Quick Links --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <div class="w-2 h-6 bg-gradient-to-b from-blue-600 to-blue-800 rounded-full mr-3"></div>
                Urutkan Video
            </h3>
        </div>
        <div class="p-6 space-y-2">
            <a href="{{ route('video.index', array_merge(request()->query(), ['sort' => 'latest'])) }}" 
               class="flex items-center p-3 rounded-lg transition-colors {{ $sort == 'latest' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'hover:bg-gray-50' }}">
                <i class="fas fa-clock mr-3 {{ $sort == 'latest' ? 'text-blue-500' : 'text-gray-400' }}"></i>
                <span class="font-medium">Terbaru</span>
            </a>
            <a href="{{ route('video.index', array_merge(request()->query(), ['sort' => 'popular'])) }}" 
               class="flex items-center p-3 rounded-lg transition-colors {{ $sort == 'popular' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'hover:bg-gray-50' }}">
                <i class="fas fa-fire mr-3 {{ $sort == 'popular' ? 'text-blue-500' : 'text-gray-400' }}"></i>
                <span class="font-medium">Terpopuler</span>
            </a>
            <a href="{{ route('video.index', array_merge(request()->query(), ['sort' => 'title'])) }}" 
               class="flex items-center p-3 rounded-lg transition-colors {{ $sort == 'title' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'hover:bg-gray-50' }}">
                <i class="fas fa-sort-alpha-down mr-3 {{ $sort == 'title' ? 'text-blue-500' : 'text-gray-400' }}"></i>
                <span class="font-medium">A-Z</span>
            </a>
        </div>
    </div>

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
        <div class="p-6 space-y-4">
            @foreach($popularVideos as $index => $popular)
            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                <div class="flex-shrink-0 w-6 h-6 bg-gradient-to-br from-orange-500 to-red-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    {{ $index + 1 }}
                </div>
                <div class="relative flex-shrink-0">
                    <img src="{{ $popular->thumbnail_hq }}" 
                         alt="{{ $popular->title }}" 
                         class="w-16 h-12 object-cover rounded-lg"
                         loading="lazy">
                    <div class="absolute bottom-1 right-1 bg-black bg-opacity-75 text-white text-xs px-1 py-0.5 rounded">
                        {{ $popular->formatted_duration }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-sm mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <a href="{{ route('video.show', $popular->video_id) }}">
                            {{ $popular->title }}
                        </a>
                    </h4>
                    <div class="text-xs text-gray-500 flex items-center">
                        <i class="fas fa-eye mr-1 text-blue-400"></i>
                        {{ $popular->formatted_view_count }} views
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>