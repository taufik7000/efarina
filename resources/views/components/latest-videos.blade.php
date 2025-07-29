{{-- Latest Videos Section Component --}}
{{-- File: resources/views/components/latest-videos.blade.php --}}

@if($latestVideos->isNotEmpty())
<section class="mb-12">
    <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-900 flex items-center">
                        <div class="w-1 h-8 bg-red-600 rounded-full mr-3"></div>
                        Video Terbaru
                    </h2>
        <a href="{{ route('video.index') }}" 
           class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-300 text-sm font-medium shadow-sm hover:shadow-md">
            <span>Lihat Semua</span>
            <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-1">
        @foreach($latestVideos as $video)
        <article class="video-card group bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-100">
            {{-- Video Thumbnail Container --}}
            <div class="relative aspect-video overflow-hidden bg-gray-100">
                <img src="{{ $video->thumbnail_hq ?? $video->thumbnail_url ?? 'https://via.placeholder.com/480x270' }}" 
                     alt="{{ $video->title }}" 
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                     loading="lazy">
                
                
                {{-- Category Badge di dalam thumbnail --}}
                @if($video->category)
                <div class="absolute top-3 left-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white/95 backdrop-blur-sm text-gray-800 shadow-lg text-sm">
                        <i class="fas fa-tag mr-1.5 text-blue-600"></i>
                        {{ Str::title($video->category->nama_kategori) }}
                    </span>
                </div>
                @endif
                
                {{-- Duration Badge --}}
                @if($video->duration_seconds)
                <div class="absolute bottom-3 right-3">
                    <span class="bg-black/80 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-lg font-medium">
                        {{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $video->duration_seconds) }}
                    </span>
                </div>
                @endif
                
            </div>

            {{-- Video Content --}}
            <div class="p-5">
                {{-- Title --}}
                <h3 class="font-semibold text-gray-900 mb-3 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2 text-sx">
                    <a href="{{ route('video.show', $video->video_id) }}" class="hover:underline">
                        {{ Str::title($video->title) }}
                    </a>
                </h3>
                
                {{-- Description --}}
                @if($video->description)
                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed text-slate-400 text-xs">
                    {{ Str::title(strip_tags($video->description), 85) }}
                </p>
                @endif
                
            
            </div>
        </article>
        @endforeach
    </div>
</section>

{{-- Enhanced CSS Styles --}}
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-video {
    aspect-ratio: 16 / 9;
}

.video-card {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.video-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
}

/* Smooth animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.video-card {
    animation: fadeInUp 0.6s ease-out;
}

/* Stagger animation for cards */
.video-card:nth-child(1) { animation-delay: 0.1s; }
.video-card:nth-child(2) { animation-delay: 0.2s; }
.video-card:nth-child(3) { animation-delay: 0.3s; }
.video-card:nth-child(4) { animation-delay: 0.4s; }

/* Custom scrollbar for better UX */
.video-card::-webkit-scrollbar {
    width: 4px;
}

.video-card::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.video-card::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.video-card::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .video-card {
        transform: none;
    }
    
    .video-card:hover {
        transform: translateY(-2px);
    }
    
    .video-card .p-5 {
        padding: 1rem;
    }
}

/* Loading state */
.video-card img {
    transition: opacity 0.3s ease;
}

.video-card img[loading="lazy"] {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}
</style>
@endif