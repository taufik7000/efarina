@extends('layouts.app')

@section('title', $video->title . ' - Portal Berita')

@section('meta')
<meta name="description" content="{{ Str::limit(strip_tags($video->display_description), 160) }}">
<meta property="og:title" content="{{ $video->title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($video->display_description), 160) }}">
<meta property="og:image" content="{{ $video->thumbnail_maxres }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="video.other">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $video->title }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($video->display_description), 160) }}">
<meta name="twitter:image" content="{{ $video->thumbnail_maxres }}">
@endsection

@section('content')
<div class="max-w-5xl mx-auto bg-gray-50 min-h-screen mt-24">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-red-600">Beranda</a>
                <span class="mx-2">/</span>
                <a href="{{ route('video.index') }}" class="hover:text-red-600">Video</a>
                @if($video->category)
                <span class="mx-2">/</span>
                <a href="{{ route('video.index', ['category' => $video->category->slug]) }}" class="hover:text-red-600">{{ $video->category->nama_kategori }}</a>
                @endif
                <span class="mx-2">/</span>
                <span class="text-gray-700">{{ Str::limit($video->title, 50) }}</span>
            </nav>
        </div>
    </div>

    <div class="px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Main Video Content --}}
            <div class="lg:col-span-2">
                {{-- Video Player --}}
                <div class="bg-black rounded-lg overflow-hidden mb-6">
                    <div class="relative aspect-video">
                        <iframe 
                            src="{{ $video->embed_url }}?autoplay=0&rel=0&showinfo=0" 
                            title="{{ $video->title }}"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            class="w-full h-full">
                        </iframe>
                    </div>
                </div>

                {{-- Video Info --}}
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">{{ $video->title }}</h1>
                    
                    {{-- Video Meta --}}
                    <div class="flex flex-wrap items-center gap-4 mb-4 text-sm text-gray-600">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-eye"></i>
                            {{ $video->formatted_view_count }} tayangan
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-clock"></i>
                            {{ $video->formatted_duration }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-calendar"></i>
                            {{ $video->published_at->format('d M Y') }}
                        </span>
                        @if($video->category)
                        <span class="px-3 py-1 text-xs font-medium text-white rounded-full" 
                              style="background-color: {{ $video->category->color }}">
                            {{ $video->category->nama_kategori }}
                        </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($video->display_description)
                    <div class="mb-6">
                        <div class="text-gray-700 whitespace-pre-line">{{ $video->display_description }}</div>
                    </div>
                    @endif

                    {{-- Tags --}}
                    @if($video->tags && count($video->tags) > 0)
                    <div class="mb-6 pt-4 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($video->tags as $tag)
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                #{{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Share Buttons --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3">Bagikan Video</h4>
                        <div class="flex flex-wrap gap-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                               target="_blank" 
                               class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($video->title) }}" 
                               target="_blank" 
                               class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                                <i class="fab fa-twitter"></i>
                                Twitter
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($video->title . ' ' . url()->current()) }}" 
                               target="_blank" 
                               class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </a>
                            <button onclick="copyToClipboard()" 
                                    class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-copy"></i>
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1">

                {{-- Related Videos --}}
                @if($relatedVideos->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        @if($video->category)
                        Video {{ $video->category->nama_kategori }} Lainnya
                        @else
                        Video Terbaru Lainnya
                        @endif
                    </h3>
                    <div class="space-y-4">
                        @foreach($relatedVideos as $related)
                        <div class="flex gap-3">
                            <div class="relative flex-shrink-0">
                                <img src="{{ $related->thumbnail_hq }}" 
                                     alt="{{ $related->title }}" 
                                     class="w-24 h-16 object-cover rounded">
                                <div class="absolute bottom-1 right-1 bg-black bg-opacity-75 text-white text-xs px-1 rounded">
                                    {{ $related->formatted_duration }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm mb-1 line-clamp-2">
                                    <a href="{{ route('video.show', $related->video_id) }}" 
                                       class="hover:text-red-600 transition-colors">
                                        {{ $related->title }}
                                    </a>
                                </h4>
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div>{{ $related->formatted_view_count }} views</div>
                                    <div>{{ $related->age }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($video->category)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('video.index', ['category' => $video->category->slug]) }}" 
                           class="text-sm text-red-600 hover:text-red-700 transition-colors">
                            Lihat semua video {{ $video->category->nama_kategori }} →
                        </a>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        showToast('Link berhasil disalin!');
    }, function(err) {
        console.error('Could not copy text: ', err);
        showToast('Gagal menyalin link');
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
@endsection