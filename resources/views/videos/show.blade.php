@extends('layouts.app')

@section('title', $video->title)

@push('styles')
<style>
    .aspect-video {
        aspect-ratio: 16 / 9;
    }
</style>
@endpush

@section('content')
<div class="bg-slate-100">
<main class="max-w-5xl mx-auto px-4 mt-24 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 lg:pt-24">

        {{-- Kolom Konten Utama (Kiri) --}}
        <div class="bg-white lg:col-span-2 mb-3">
            <div class="rounded-xl shadow-md overflow-hidden">
                <div class="aspect-video bg-black">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $video->video_id }}?autoplay=1&rel=0" title="{{ $video->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-6">
                    <h1 class="text-2xl lg:text-2xl font-bold text-gray-600 leading-tight">{{ $video->title }}</h1>
                    <div class="flex items-center text-sm text-gray-500 mt-3 space-x-4">
                        <div class="flex items-center"><i class="fas fa-clock mr-2"></i><span>{{ \Carbon\Carbon::parse($video->published_at)->diffForHumans() }}</span></div>
                        @if($video->category)
                        <div class="flex items-center">
                             <a href="{{ route('video.index', ['category' => $video->category->slug]) }}" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full hover:bg-blue-200"><i class="fas fa-tag mr-1"></i>{{ $video->category->name }}</a>
                        </div>
                        @endif
                    </div>
                    <div class="prose max-w-none mt-6 text-slate-500">{!! nl2br(e($video->description)) !!}</div>
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">BAGIKAN:</h4>
                        <div class="flex space-x-2">
                            <a href="#" class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="w-10 h-10 flex items-center justify-center bg-black text-white rounded-full hover:bg-gray-800"><i class="fab fa-x-twitter"></i></a>
                            <a href="#" class="w-10 h-10 flex items-center justify-center bg-green-500 text-white rounded-full hover:bg-green-600"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-400 text-white rounded-full hover:bg-gray-500"><i class="fas fa-link"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === BAGIAN VIDEO TERBARU DENGAN LOAD MORE (GRID) === --}}
            <div class="bg-white rounded-xl shadow-md mt-18 p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Terbaru Lainnya</h3>
            
                {{-- DIUBAH: Kontainer diubah menjadi grid --}}
                <div id="latest-videos-container" class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-6">
                    {{-- Video akan dimuat di sini oleh JavaScript --}}
                </div>
            
                {{-- Tombol Load More & Wrapper-nya --}}
                <div id="load-more-wrapper" class="mt-6 text-center">
                    <button id="load-more-btn" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 transition-all focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:bg-gray-400" data-page="1" data-exclude="{{ $video->video_id }}">
                        <span id="btn-text">Muat Lebih Banyak</span>
                        <span id="btn-loader" class="hidden"><i class="fas fa-spinner fa-spin"></i> Memuat...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom Sidebar (Kanan) --}}
        <aside class="lg:col-span-1 space-y-6">
             @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])
        </aside>

    </div>
</main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreWrapper = document.getElementById('load-more-wrapper');
    const videosContainer = document.getElementById('latest-videos-container');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');
    
    // === DIUBAH: Fungsi ini sekarang menghasilkan elemen HTML dengan gaya GRID ===
    function createVideoElement(video) {
        function timeAgo(dateString) {
            const date = new Date(dateString);
            const seconds = Math.floor((new Date() - date) / 1000);
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " tahun lalu";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " bulan lalu";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + " hari lalu";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " jam lalu";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " menit lalu";
            return Math.floor(seconds) + " detik lalu";
        }

        const videoUrl = `{{ url('video') }}/${video.video_id}`;
        const title = video.title.toLowerCase().replace(/\b\w/g, s => s.toUpperCase()).substring(0, 60) + (video.title.length > 60 ? '...' : '');
        const viewsFormatted = new Intl.NumberFormat('id-ID').format(video.views);

        return `
            <div class="flex flex-col">
                <a href="${videoUrl}" class="block mb-2">
                    <img class="w-full aspect-video object-cover rounded-lg shadow-md hover:shadow-xl transition-shadow" 
                         src="${video.thumbnail_url}" 
                         alt="${video.title}">
                </a>
                <h3 class="text-sm font-semibold text-gray-800 leading-snug">
                    <a href="${videoUrl}" class="hover:text-blue-600">${title}</a>
                </h3>
                <div class="flex items-center text-xs text-gray-500 mt-2">
                    <span class="flex items-center"><i class="fas fa-clock mr-1.5"></i> ${timeAgo(video.published_at)}</span>
                </div>
            </div>
        `;
    }

    function loadVideos() {
        if (!loadMoreBtn) return;
        let page = parseInt(loadMoreBtn.getAttribute('data-page'));
        const excludeId = loadMoreBtn.getAttribute('data-exclude');

        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
        loadMoreBtn.disabled = true;

        const apiUrl = `{{ route('api.video.index') }}?page=${page}&exclude=${excludeId}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    const videosHtml = data.data.map(createVideoElement).join('');
                    videosContainer.insertAdjacentHTML('beforeend', videosHtml);
                    
                    if (!data.next_page_url) {
                        if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
                    } else {
                        loadMoreBtn.setAttribute('data-page', page + 1);
                    }
                } else {
                    if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Gagal memuat video:', error);
                if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
            })
            .finally(() => {
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
                loadMoreBtn.disabled = false;
            });
    }

    loadVideos();
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadVideos);
    }
});
</script>
@endpush