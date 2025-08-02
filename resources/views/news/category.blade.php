@extends('layouts.app')

@section('title', 'Kategori: ' . $category->nama_kategori . ' - Efarina TV')

{{-- 
    PERBAIKAN: @push('styles') sekarang berada di luar @section('content')
--}}
@push('styles')
<style>
    /* Style untuk loading state */
    #news-list-container.loading { opacity: 0.5; transition: opacity 0.3s; }
    /* Style untuk hero section */
    .hero-card-main { height: 450px; }
    .hero-card-side { height: 217px; }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush


@section('content')
{{-- Header Kategori --}}
<div class="bg-white border-b border-gray-200 lg:mt-[180px]">
    <div class="max-w-5xl mx-auto px-4">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 py-3">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('news.index') }}" class="hover:text-blue-600">Berita</a>
            <span class="mx-2">/</span>
            <span class="text-gray-700 font-medium">{{ $category->nama_kategori }}</span>
        </nav>
        {{-- Judul Kategori --}}
        <div class="flex items-center gap-4 py-4 border-t border-gray-100">
            <span class="w-1.5 h-8 rounded-full" style="background-color: {{ $category->color ?? '#be123c' }}"></span>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">
                {{ $category->nama_kategori }}
            </h1>
        </div>
    </div>
</div>

{{-- === HERO SECTION YANG DIPERBAIKI === --}}
@if(isset($heroNews) && $heroNews->count() >= 3)
<section class="max-w-5xl mx-auto px-4 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @php
            // Ambil berita pertama untuk kartu utama, dan 2 sisanya untuk kartu samping
            $mainNews = $heroNews->first();
            $sideNews = $heroNews->skip(1)->take(2);
        @endphp

        {{-- Berita Utama (Kartu Besar di Kiri) --}}
        <div class="lg:col-span-2">
            <a href="{{ route('news.show', $mainNews->slug) }}" class="relative block w-full h-full min-h-[400px] lg:min-h-[450px] group rounded-lg overflow-hidden shadow-lg">
                {{-- Gambar Latar --}}
                <img src="{{ $mainNews->thumbnail ? asset('storage/' . $mainNews->thumbnail) : 'https://via.placeholder.com/800x450' }}"
                     alt="{{ $mainNews->judul }}"
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                
                {{-- Overlay Gradien untuk Keterbacaan Teks --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                
                {{-- Konten Teks di Atas Gambar --}}
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    @if($mainNews->category)
                    <span class="inline-block px-3 py-1 text-xs font-semibold text-white rounded-full mb-2" style="background-color: {{ $mainNews->category->color ?? '#be123c' }}">
                        {{ $mainNews->category->nama_kategori }}
                    </span>
                    @endif
                    <h2 class="text-xl lg:text-2xl font-bold leading-tight line-clamp-3 group-hover:text-gray-200 transition-colors [text-shadow:_7px_5px_20px_#666666]">
                        {{ Str::title(strtolower($mainNews->judul)) }}
                    </h2>
                    <div class="mt-2 text-xs opacity-80">
                        <span>{{ \Carbon\Carbon::parse($mainNews->published_at)->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- Berita Samping (Dua Kartu Kecil di Kanan) --}}
        <div class="flex flex-col gap-4">
            @foreach($sideNews as $sNews)
            <div class="flex-1">
                <a href="{{ route('news.show', $sNews->slug) }}" class="relative block h-full min-h-[200px] lg:min-h-[215px] group rounded-lg overflow-hidden shadow-lg">
                    {{-- Gambar Latar --}}
                    <img src="{{ $sNews->thumbnail ? asset('storage/' . $sNews->thumbnail) : 'https://via.placeholder.com/400x225' }}"
                         alt="{{ $sNews->judul }}"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    
                    {{-- Overlay Gradien --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    
                    {{-- Konten Teks --}}
                    <div class="absolute bottom-0 left-0 p-4 text-white">
                        @if($sNews->category)
                        <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full mb-1" style="background-color: {{ $sNews->category->color ?? '#be123c' }}">
                            {{ $sNews->category->nama_kategori }}
                        </span>
                        @endif
                        <h3 class="text-sm lg:text-md font-bold leading-tight line-clamp-2 group-hover:text-gray-200 transition-colors text-shadow-lg/50">
                            {{ Str::title(strtolower($sNews->judul)) }}
                        </h3>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
{{-- === AKHIR HERO SECTION === --}}


{{-- Konten Utama --}}
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Kolom Utama (Daftar Berita) --}}
        <div class="lg:col-span-2">
            <div id="news-list-container" class="space-y-4">
                @include('news.components.news-list-item', ['newsItems' => $news])
            </div>

            @if($news->hasMorePages())
            <div id="load-more-wrapper" class="mt-8 text-center">
                <button id="load-more-btn" class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-blue-700 transition-all">
                    <span id="btn-text">Tampilkan Lebih Banyak</span>
                    <span id="btn-loader" class="hidden"><i class="fas fa-spinner fa-spin"></i> Memuat...</span>
                </button>
            </div>
            @endif
        </div>

        {{-- Sidebar (Berita Populer) --}}
        <aside class="lg:col-span-1 space-y-6">
            @include('news.sidebar', [
                'news' => $news, 
                'relatedNews' => $relatedNews ?? [], 
                'popularNews' => $popularNews ?? []
            ])
            @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])
        </aside>
    </div>
</div>
@endsection

{{-- 
    PERBAIKAN: @push('scripts') sekarang berada di luar @section('content')
--}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = {{ $news->currentPage() }};
    const categorySlug = '{{ $category->slug }}';
    let isLoading = false;

    const newsContainer = document.getElementById('news-list-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreWrapper = document.getElementById('load-more-wrapper');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    function fetchNews() {
        if (isLoading || !loadMoreBtn) return;
        isLoading = true;
        currentPage++;

        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');

        let apiUrl = `{{ route('api.news.load_more') }}?page=${currentPage}&category=${categorySlug}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.html.trim() !== '') {
                    newsContainer.insertAdjacentHTML('beforeend', data.html);
                }
                if (!data.hasMorePages) {
                    if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching news:', error))
            .finally(() => {
                isLoading = false;
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
            });
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', fetchNews);
    }
});
</script>
@endpush