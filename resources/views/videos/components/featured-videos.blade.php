{{-- Featured Videos Component --}}
@if($featuredVideos->isNotEmpty())
<section class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <div class="w-2 h-6 bg-gradient-to-b from-blue-600 to-blue-800 rounded-full mr-3"></div>
            Video Unggulan
        </h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($featuredVideos->take(2) as $featured)
            <div class="group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg hover:border-blue-200 transition-all duration-300">
                <div class="relative">
                    <img src="{{ $featured->thumbnail_hq }}" 
                         alt="{{ $featured->title }}" 
                         class="w-full h-48 object-cover"
                         loading="lazy">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center">
                        <a href="{{ route('video.show', $featured->video_id) }}" 
                           class="opacity-0 group-hover:opacity-100 transition-all duration-300 bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700">
                            <i class="fas fa-play text-lg"></i>
                        </a>
                    </div>
                    <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                        {{ $featured->formatted_duration }}
                    </div>
                    <div class="absolute top-2 left-2">
                        <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-2 py-1 rounded text-xs font-medium">
                            <i class="fas fa-star mr-1"></i>UNGGULAN
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-base mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <a href="{{ route('video.show', $featured->video_id) }}">
                            {{ $featured->title }}
                        </a>
                    </h3>
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                        {{ $featured->display_description }}
                    </p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-eye mr-1 text-blue-500"></i>{{ $featured->formatted_view_count }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-clock mr-1 text-gray-400"></i>{{ $featured->age }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif