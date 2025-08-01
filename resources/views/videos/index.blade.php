@extends('layouts.app')

@section('title', 'Tayangan Video Terbaru - Efarina TV')

@push('styles')
<style>
/* Modern Video Portal Styles */
:root {
    --primary-red: #dc2626;
    --primary-blue: #1e40af; /* Biru utama yang bisa digunakan */
    --dark-bg: #1f2937;
    --light-gray: #f8fafc;
    --border-gray: #e5e7eb;
}

/* ... (Kode CSS Anda yang lain tetap di sini, tidak perlu diubah) ... */
.container {
    max-width: 1000px;
}
.hero-section {
    position: relative;
    overflow: hidden;
    padding: 2rem 0 2rem 0;
    margin-top: 50px;
}
.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}
.hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}
.hero-breadcrumb {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    position: relative;
    z-index: 10;
}
.hero-title {
    color: white;
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    z-index: 10;
}
.hero-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #ef4444, #f97316);
    border-radius: 2px;
}
.featured-video-card {
    position: relative;
    overflow: hidden;
    border-radius: 0.75rem;
    background: white;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    z-index: 10;
}
.featured-video-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}
.video-card {
    background: white;
    margin-bottom: 1rem;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 10;
    position: relative;
}
.video-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}
.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}
.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary-red);
    transition: all 0.3s ease;
    opacity: 0;
}
.featured-video-card:hover .play-button,
.video-card:hover .play-button {
    opacity: 1;
}
.play-button:hover {
    background: white;
    transform: translate(-50%, -50%) scale(1.1);
}
.trending-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    background: var(--primary-red);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.duration-badge {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.hero-section .bg-white {
    background: transparent !important;
    border-bottom: none !important;
}
.hero-section .max-w-7xl {
    max-width: 1150px;
}
.hero-section nav {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 2rem;
}
.hero-section nav a,
.hero-section nav span {
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}
.hero-section nav a:hover {
    color: #fbbf24 !important;
}
.hero-section nav .fas.fa-chevron-right {
    color: rgba(255, 255, 255, 0.7) !important;
}
@media (max-width: 768px) {
    .hero-section {
        padding: 1.5rem 0 2rem 0;
    }
    .hero-breadcrumb {
        margin-bottom: 1.5rem;
        border-radius: 0.375rem;
    }
    .featured-video-card {
        margin-bottom: 1rem;
    }
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .mobile-thumbnail {
        width: 120px !important;
        height: 90px !important;
    }
    .hero-title {
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .hero-title + p {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    .mobile-title {
        font-size: 0.9rem;
        line-height: 1.3;
        margin-bottom: 0.5rem;
    }
    .mobile-excerpt {
        font-size: 0.7rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }
    .mobile-meta {
        font-size: 0.7rem;
    }
    .mobile-content {
        padding: 0.75rem;
    }
}
@media (min-width: 769px) and (max-width: 1024px) {
    .tablet-thumbnail {
        width: 140px;
        height: 105px;
    }
}
@media (min-width: 1025px) {
    .desktop-thumbnail {
        width: 192px;
        height: 128px;
    }
}
input:focus, select:focus, button:focus, a:focus {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* === [PERUBAHAN] CATEGORY TABS THEME BIRU TUA === */
.category-tabs-wrapper {
    position: relative;
    border-radius: 1rem;
    padding: 0.375rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.category-tabs {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    -ms-overflow-style: none;
    scrollbar-width: none;
    padding: 0.25rem 3rem; /* Ruang untuk panah */
}

.category-tabs::-webkit-scrollbar {
    display: none;
}

.tab-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    /* Diubah: Warna teks default */
    color: #dbeafe; 
    background: transparent;
    border: none;
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
    min-height: 2.75rem;
    text-transform: capitalize;
}

/* Diubah: Hover effect dengan warna biru lebih cerah */
.tab-item:hover {
    color: #ffffff;
    background: rgba(37, 99, 235, 0.5); /* Biru lebih terang saat hover */
    transform: translateY(-1px);
}

/* Diubah: Active state sekarang hanya mengatur style teks. Warna background diatur via JS */
.tab-item.active {
    color: #ffffff;
    font-weight: 600;
   
    /* Dihapus: Properti background statis (merah) dihapus */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Diubah: Scroll arrows disesuaikan dengan tema biru */
.scroll-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    /* Diubah: Warna background panah */
    background: rgb(85 85 85 / 90%); 
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    /* Diubah: Warna ikon panah */
    color: #93c5fd; 
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.scroll-arrow:hover {
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
/* ... (CSS lain di bawah sini tetap sama) ... */
.scroll-arrow.left { left: 0.5rem; }
.scroll-arrow.right { right: 0.5rem; }
.scroll-arrow.hidden {
    opacity: 0;
    pointer-events: none;
    transform: translateY(-50%) scale(0.8);
}
#video-grid-container.loading {
    opacity: 0.6;
    transition: opacity 0.3s ease;
    pointer-events: none;
}
@media (max-width: 1024px) {
    .category-tabs-wrapper { border-radius: 1.75rem; padding: 0.3rem; }
    .category-tabs { padding: 0.25rem 2.5rem; }
    .tab-item { padding: 0.625rem 1.25rem; font-size: 0.8rem; min-height: 2.5rem; }
    .scroll-arrow { width: 2.25rem; height: 2.25rem; }
}
@media (max-width: 768px) {
    .category-tabs-wrapper {
        background: #1e3a8a; /* Warna biru solid untuk mobile */
        border-radius: 1.5rem; margin-bottom: 1.5rem; padding: 0.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .category-tabs { padding: 0.25rem 1rem; gap: 0.375rem; }
    .scroll-arrow { display: none !important; }
    .tab-item { padding: 0.5rem 1rem; font-size: 0.75rem; min-height: 2.25rem; border-radius: 1.25rem; }
    .tab-item:hover { transform: none; }
    .tab-item.active { transform: none; box-shadow: 0 2px 8px rgba(0,0,0, 0.25); }
}
@media (max-width: 480px) {
    .category-tabs-wrapper {
        margin-left: -1rem; margin-right: -1rem; border-radius: 0;
        background: #1e3a8a;
    }
    .tab-item { padding: 0.375rem 0.875rem; font-size: 0.7rem; min-height: 2rem; }
}
.tab-item:focus, .scroll-arrow:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

</style>
@endpush

@section('content')
<div class="bg-slate-200">
    {{-- Hero Section dengan Video Unggulan --}}
    <section class="hero-section mt-24">
        <div class="container mx-auto relative mt:0 lg:mt-24">
            @if($featuredVideos->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
                {{-- Main Featured Video --}}
                <div class="lg:col-span-2 max-h-[330px] relative">
                    @php $mainFeatured = $featuredVideos->first(); @endphp
                    <div class="featured-video-card h-full relative">
                        <img src="{{ $mainFeatured->thumbnail_maxres ?? $mainFeatured->thumbnail_hq ?? 'https://via.placeholder.com/800x450' }}" 
                             alt="{{ $mainFeatured->title }}" 
                             class="w-full h-full object-cover rounded-lg">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/80 to-transparent rounded-lg"></div>
                        <a href="{{ route('video.show', $mainFeatured->video_id) }}" class="play-button"><i class="fas fa-play"></i></a>
                        @if($mainFeatured->duration_seconds)
                        <div class="duration-badge">{{ gmdate($mainFeatured->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $mainFeatured->duration_seconds) }}</div>
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                            @if($mainFeatured->category)
                            <span class="category-badge mb-2" style="background-color: {{ $mainFeatured->category->color }}; color: white;">
                                {{ $mainFeatured->category->nama_kategori }}
                            </span>
                            @endif
                            <h1 class="text-xl lg:text-3xl font-bold mb-2 leading-tight">
                                <a href="{{ route('video.show', $mainFeatured->video_id) }}" class="hover:text-red-300 transition-colors">{{ $mainFeatured->title }}</a>
                            </h1>
                            <div class="flex items-center text-sm text-gray-300">
                                <i class="fas fa-calendar mr-2"></i>{{ $mainFeatured->published_at ? $mainFeatured->published_at->format('d M Y') : 'N/A' }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-eye mr-2"></i>{{ number_format($mainFeatured->view_count ?? 0) }} views
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Sidebar Video Unggulan --}}
                <div class="lg:col-span-1">
                    @foreach($featuredVideos->skip(1)->take(3) as $index => $video)
                    <div class="video-card group">
                        <div class="flex">
                            <div class="relative flex-shrink-0 w-32 h-20 lg:w-40 lg:h-24">
                                <img src="{{ $video->thumbnail_hq ?? 'https://via.placeholder.com/300x170' }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                                <a href="{{ route('video.show', $video->video_id) }}" class="play-button" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-play"></i></a>
                                @if($video->duration_seconds)
                                <div class="duration-badge text-xs">{{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $video->duration_seconds) }}</div>
                                @endif
                            </div>
                            <div class="flex-1 p-3">
                                <h3 class="font-semibold text-sm text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <a href="{{ route('video.show', $video->video_id) }}">{{ \Str::title($video->title) }}</a>
                                </h3>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>{{ $video->published_at ? $video->published_at->format('d M') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="category-tabs-wrapper bg-blue-950">
            <div class="category-tabs" id="category-tabs">
                {{-- Diubah: Tab "Semua" diberi data-color default --}}
                <button class="tab-item active" data-category="all" data-color="#2563eb">Semua</button>
                @foreach($categories as $category)
                    @if($category->videos_count > 0)
                        {{-- Diubah: Ditambahkan atribut data-color --}}
                        <button class="tab-item" data-category="{{ $category->slug }}" data-color="{{ $category->color }}">{{ $category->nama_kategori }}</button>
                    @endif
                @endforeach
            </div>
            {{-- Panah scroll akan ditambahkan di sini oleh JavaScript --}}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
            <div class="lg:col-span-3 space-y-8">
                <div id="video-grid-container">
                    @include('videos.components.video-grid', ['videos' => $videos])
                </div>
                <div id="pagination-container">
                    @include('videos.components.pagination', ['videos' => $videos, 'paginationInfo' => $paginationInfo])
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabsContainer = document.querySelector('.category-tabs');
    const videoGridContainer = document.getElementById('video-grid-container');
    const paginationContainer = document.getElementById('pagination-container');
    let currentCategory = 'all';

    // ... (Fungsi scroll dan arrow tidak diubah) ...
    function initializeScrollArrows() {
        const tabsWrapper = document.querySelector('.category-tabs-wrapper');
        if (!tabsWrapper) return;
        if (!tabsWrapper.querySelector('.scroll-arrow')) {
            const leftArrow = document.createElement('button');
            leftArrow.className = 'scroll-arrow left';
            leftArrow.innerHTML = '<i class="fas fa-chevron-left"></i>';
            leftArrow.setAttribute('aria-label', 'Scroll left');
            const rightArrow = document.createElement('button');
            rightArrow.className = 'scroll-arrow right';
            rightArrow.innerHTML = '<i class="fas fa-chevron-right"></i>';
            rightArrow.setAttribute('aria-label', 'Scroll right');
            tabsWrapper.appendChild(leftArrow);
            tabsWrapper.appendChild(rightArrow);
            leftArrow.addEventListener('click', () => scrollTabs('left'));
            rightArrow.addEventListener('click', () => scrollTabs('right'));
        }
        updateArrowVisibility();
    }
    function scrollTabs(direction) {
        const scrollAmount = 200;
        tabsContainer.scrollTo({
            left: tabsContainer.scrollLeft + (direction === 'left' ? -scrollAmount : scrollAmount),
            behavior: 'smooth'
        });
    }
    function updateArrowVisibility() {
        const leftArrow = document.querySelector('.scroll-arrow.left');
        const rightArrow = document.querySelector('.scroll-arrow.right');
        if (!leftArrow || !rightArrow) return;
        const isScrollable = tabsContainer.scrollWidth > tabsContainer.clientWidth;
        const isAtStart = tabsContainer.scrollLeft < 1;
        const isAtEnd = Math.abs(tabsContainer.scrollWidth - tabsContainer.clientWidth - tabsContainer.scrollLeft) < 1;
        if (!isScrollable) {
            leftArrow.classList.add('hidden');
            rightArrow.classList.add('hidden');
        } else {
            leftArrow.classList.toggle('hidden', isAtStart);
            rightArrow.classList.toggle('hidden', isAtEnd);
        }
    }
    if (tabsContainer) {
        tabsContainer.addEventListener('scroll', updateArrowVisibility);
        window.addEventListener('resize', () => setTimeout(updateArrowVisibility, 100));
        tabsContainer.addEventListener('wheel', function(e) {
            if (tabsContainer.scrollWidth > tabsContainer.clientWidth) {
                e.preventDefault();
                tabsContainer.scrollLeft += e.deltaY;
            }
        });
    }

    function fetchVideos(page = 1) {
        const urlParams = new URLSearchParams(window.location.search);
        const sort = urlParams.get('sort') || 'latest';
        const search = urlParams.get('search') || '';
        let apiUrl = `{{ route('api.video.renderedGrid') }}?page=${page}&sort=${sort}`;
        if (currentCategory !== 'all') apiUrl += `&category=${currentCategory}`;
        if (search) apiUrl += `&search=${search}`;
        videoGridContainer.classList.add('loading');
        paginationContainer.innerHTML = '';
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                videoGridContainer.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination_html;
            })
            .catch(error => {
                console.error('Error fetching videos:', error);
                videoGridContainer.innerHTML = '<p class="text-center text-red-500">Gagal memuat video. Silakan coba lagi.</p>';
            })
            .finally(() => videoGridContainer.classList.remove('loading'));
    }

    // === [PERUBAHAN] FUNGSI UNTUK MENGATUR WARNA TAB AKTIF ===
    function setActiveTab(tabElement) {
        // Reset semua tab terlebih dahulu
        tabsContainer.querySelectorAll('.tab-item').forEach(tab => {
            tab.classList.remove('active');
            tab.style.backgroundColor = ''; // Hapus inline style background
            tab.style.boxShadow = '';
        });

        // Terapkan style ke tab yang aktif
        tabElement.classList.add('active');
        const activeColor = tabElement.dataset.color;
        if (activeColor) {
            tabElement.style.backgroundColor = activeColor;
            // Opsi: Tambahkan shadow yang lebih lembut berdasarkan warna
            // tabElement.style.boxShadow = `0 4px 14px 0 ${activeColor}55`;
        }
    }

    if (tabsContainer) {
        tabsContainer.addEventListener('click', function(e) {
            const clickedTab = e.target.closest('.tab-item');
            if (clickedTab) {
                // Jangan lakukan apa-apa jika tab yang diklik sudah aktif
                if (clickedTab.classList.contains('active')) {
                    return;
                }
                
                setActiveTab(clickedTab);
                
                currentCategory = clickedTab.dataset.category;
                fetchVideos(1);

                scrollActiveTabIntoView(clickedTab);
            }
        });
    }

    function scrollActiveTabIntoView(activeTab) {
        const tabRect = activeTab.getBoundingClientRect();
        const containerRect = tabsContainer.getBoundingClientRect();
        const scrollOffset = (tabRect.left + tabRect.width / 2) - (containerRect.left + containerRect.width / 2);
        tabsContainer.scrollTo({
            left: tabsContainer.scrollLeft + scrollOffset,
            behavior: 'smooth'
        });
        setTimeout(updateArrowVisibility, 300);
    }
    
    document.body.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('#pagination-container a');
        if (paginationLink) {
            e.preventDefault();
            if (!paginationLink.href || paginationLink.closest('.disabled, .active')) return;
            try {
                const url = new URL(paginationLink.href);
                const page = url.searchParams.get('page');
                if (page) {
                    fetchVideos(page);
                    videoGridContainer.scrollIntoView({ behavior: 'smooth' });
                }
            } catch (error) {
                console.error('Invalid pagination URL:', error);
            }
        }
    });

    // === [PERUBAHAN] INISIALISASI ===
    initializeScrollArrows();
    // Atur warna tab aktif saat halaman pertama kali dimuat
    const initialActiveTab = tabsContainer.querySelector('.tab-item.active');
    if (initialActiveTab) {
        setActiveTab(initialActiveTab);
    }
});
</script>
@endpush
@endsection