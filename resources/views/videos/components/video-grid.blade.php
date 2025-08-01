{{-- Video Grid Component --}}
@if($videos->isNotEmpty())
<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($videos as $video)
    <div class="group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg hover:border-blue-200 transition-all duration-300">
        <div class="relative">
            <img src="{{ $video->thumbnail_hq }}" 
                 alt="{{ $video->title }}" 
                 class="w-full h-40 object-cover"
                 loading="lazy">
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center">
                <a href="{{ route('video.show', $video->video_id) }}" 
                   class="opacity-0 group-hover:opacity-100 transition-all duration-300 bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700">
                    <i class="fas fa-play text-lg"></i>
                </a>
            </div>
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                {{ $video->formatted_duration }}
            </div>
            @if($video->category)
            <div class="absolute top-2 left-2">
                <span class="px-2 py-1 text-xs font-medium text-white rounded" 
                      style="background-color: {{ $video->category->color }};">
                    {{ $video->category->nama_kategori }}
                </span>
            </div>
            @endif
            @if($video->is_featured)
            <div class="absolute top-2 right-2">
                <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-2 py-1 text-xs font-medium rounded">
                    <i class="fas fa-star mr-1"></i>TOP
                </span>
            </div>
            @endif
        </div>
        <div class="p-4">
            <h3 class="font-semibold text-sm mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                <a href="{{ route('video.show', $video->video_id) }}">
                    {{ \Str::title($video->title) }}
                </a>
            </h3>
            <p class="text-gray-600 text-xs mb-3 line-clamp-2">
                {{ Str::limit($video->display_description, 100) }}
            </p>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span class="flex items-center">
                    <i class="fas fa-eye mr-1 text-blue-500"></i>{{ $video->formatted_view_count }}
                </span>
                <span class="flex items-center">
                    <i class="fas fa-calendar mr-1 text-gray-400"></i>{{ $video->age }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <div class="text-gray-400 mb-6">
        <i class="fas fa-video text-6xl"></i>
    </div>
    <h3 class="text-xl font-semibold text-gray-700 mb-4">Tidak ada video ditemukan</h3>
    <p class="text-gray-500 mb-6">
        @if(request('search'))
        Coba ubah kata kunci pencarian Anda atau hapus filter.
        @else
        Belum ada video yang tersedia saat ini.
        @endif
    </p>
    @if(request('search') || request('category') || request('sort') != 'latest')
    <a href="{{ route('video.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-refresh mr-2"></i>Lihat Semua Video
    </a>
    @endif
</div>
@endif