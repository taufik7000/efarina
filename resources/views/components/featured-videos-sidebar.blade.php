{{-- Sidebar Featured Videos Widget --}}
{{-- File: resources/views/components/featured-videos-sidebar.blade.php --}}

@if($featuredVideos->isNotEmpty())
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-star text-yellow-500 mr-2"></i>
        Video Unggulan
    </h3>
    
    <div class="space-y-4">
        @foreach($featuredVideos->take(5) as $index => $video)
        <div class="group relative bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-red-300 hover:shadow-md transition-all duration-300">
            {{-- Video Thumbnail --}}
            <div class="relative overflow-hidden">
                <img src="{{ $video->thumbnail_hq ?? $video->thumbnail_url ?? 'https://via.placeholder.com/300x170' }}" 
                     alt="{{ $video->title }}" 
                     class="w-full h-32 object-cover transition-transform duration-300 group-hover:scale-105"
                     loading="lazy">
                
                {{-- Play Button Overlay --}}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center">
                    <a href="{{ route('video.show', $video->video_id) }}" 
                       class="opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                        <div class="w-12 h-12 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-play text-white text-sm ml-0.5"></i>
                        </div>
                    </a>
                </div>
                
                {{-- Duration Badge --}}
                @if($video->duration_seconds)
                <div class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded font-medium">
                    {{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $video->duration_seconds) }}
                </div>
                @endif
                
                {{-- Featured Badge --}}
                <div class="absolute top-2 left-2">
                    <span class="bg-gradient-to-r from-blue-500 to-dark-blue-1000 text-white px-2 py-1 rounded text-xs font-bold">
                        <i class="fas fa-star mr-1"></i>{{ $index + 1 }}
                    </span>
                </div>
            </div>

            {{-- Video Info --}}
            <div class="p-3">
                {{-- Category --}}
                @if($video->category)
                <div class="mb-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ Str::title($video->category->nama_kategori) }}
                </span>
                </div>
                @endif
                
                {{-- Title --}}
                <h4 class="font-semibold text-gray-900 text-sm leading-tight mb-2 group-hover:text-red-600 transition-colors line-clamp-2">
                    <a href="{{ route('video.show', $video->video_id) }}" class="hover:underline">
                        {{ Str::title($video->title, 60) }}
                    </a>
                </h4>
                
                {{-- Meta Info --}}
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <span>{{ $video->published_at ? $video->published_at->format('d M') : $video->created_at->format('d M') }}</span>
                    </div>
                    @if(isset($video->view_count))
                    <div class="flex items-center">
                        <i class="fas fa-eye mr-1"></i>
                        <span>{{ number_format($video->view_count) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- CSS untuk line-clamp --}}
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endif