{{-- Komponen ini hanya berisi perulangan untuk menampilkan video --}}
@foreach($videos as $video)
<a href="{{ route('video.show', $video->video_id) }}" class="flex items-start space-x-4 group p-3 -mx-3 rounded-lg hover:bg-gray-100 transition-colors">
    <div class="w-28 flex-shrink-0">
        <img class="w-full h-16 object-cover rounded-md" 
             src="{{ $video->thumbnail_url }}" 
             alt="{{ $video->title }}">
    </div>
    <div>
        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 leading-tight transition-colors">
            {{ Str::limit($video->title, 55) }}
        </h4>
        <div class="flex items-center text-xs text-gray-500 mt-1">
            <i class="fas fa-clock mr-1.5"></i>
            <span>{{ \Carbon\Carbon::parse($video->published_at)->diffForHumans() }}</span>
        </div>
    </div>
</a>
@endforeach