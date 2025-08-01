{{-- Updated home.blade.php dengan Hero Section + Video Unggulan di Sidebar --}}
@extends('layouts.app')

@section('title', 'Efarina TV - Live Streaming & Berita Sumut Terbaru')

@push('styles')
<style>
/* Modern News Portal Styles */
:root {
    --primary-red: #dc2626;
    --primary-blue: #1e40af;
    --dark-bg: #1f2937;
    --light-gray: #f8fafc;
    --border-gray: #e5e7eb;
}

/* Container consistency */
.container {
    max-width: 1024px;
}



.featured-card {
    position: relative;
    overflow: hidden;
    border-radius: 0.75rem;
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.featured-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.news-card {
    background: white;
    margin-bottom: 1.1rem;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid var(--border-gray);
}

.news-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
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

.video-card {
    position: relative;
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.video-card:hover {
    transform: scale(1.02);
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

@media (max-width: 768px) {
    .hero-section {
        padding: 1rem 0;
    }
    
    .featured-card {
        margin-bottom: 1rem;
    }
    
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Enhanced Mobile Styles - Maintains side-by-side layout */
@media (max-width: 768px) {
    .news-card, .featured-card {
        margin-bottom: 1rem;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    
    /* Mobile thumbnail sizing */
    .mobile-thumbnail {
        width: 120px !important;
        height: 90px !important;
    }
    
    /* Better text sizing on mobile */
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
    
    /* Ensure proper spacing on mobile */
    .mobile-content {
        padding: 0.75rem;
    }
}

/* Tablet and small desktop */
@media (min-width: 769px) and (max-width: 1024px) {
    .tablet-thumbnail {
        width: 140px;
        height: 105px;
    }
}

/* Desktop specific styles */
@media (min-width: 1025px) {
    .desktop-thumbnail {
        width: 192px;
        height: 128px;
    }
}

/* General image styles */
.news-card img, .featured-card img {
    border-top-left-radius: 0.35rem;
    border-bottom-left-radius: 0.35rem;
}
</style>
@endpush

@section('content')

{{-- Hero Section dengan Berita Unggulan --}}
<section class="hero-section">
    <div class="container mx-auto px-4 mt-24">
        @if($featuredNews->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            {{-- Main Featured News (Tidak ada perubahan di sini) --}}
            <div class="lg:col-span-2">
                @php $mainFeatured = $featuredNews->first(); @endphp
                <div class="featured-card h-full relative">
                    <img src="{{ $mainFeatured->thumbnail ? asset('storage/' . $mainFeatured->thumbnail) : 'https://via.placeholder.com/800x450' }}" 
                         alt="{{ $mainFeatured->judul }}" 
                         class="w-full h-full object-cover rounded-lg">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent rounded-lg"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        <span class="category-badge mb-2" style="background-color: {{ $mainFeatured->category->color }}; color: white;">
                            {{ $mainFeatured->category->nama_kategori }}
                        </span>
                        <h1 class="text-xl lg:text-3xl font-bold mb-2 leading-tight">
                            <a href="{{ route('news.show', $mainFeatured->slug) }}" class="hover:text-red-300 transition-colors">
                                {{ $mainFeatured->judul }}
                            </a>
                        </h1>
                        <div class="flex items-center text-sm text-gray-300">
                            <i class="fas fa-clock mr-2"></i>
                            {{ $mainFeatured->published_at ? $mainFeatured->published_at->format('d M Y') : $mainFeatured->created_at->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary Featured News (BAGIAN YANG DIUBAH) --}}
            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4">
                @foreach($featuredNews->skip(1)->take(2) as $news)
                {{-- Kartu Berita Sekunder --}}
                <div class="featured-card h-64 relative"> {{-- Tinggi kartu diatur di sini --}}
                    <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                         alt="{{ $news->judul }}" 
                         class="w-full h-full object-cover rounded-lg">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent rounded-lg"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                        <span class="category-badge mb-1 text-xs" style="background-color: {{ $news->category->color }}; color: white;">
                            {{ $news->category->nama_kategori }}
                        </span>
                        <h3 class="font-bold text-md leading-tight">
                            <a href="{{ route('news.show', $news->slug) }}" class="hover:text-red-300 transition-colors">
                                {{ Str::limit($news->judul, 60) }}
                            </a>
                        </h3>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        @endif
    </div>
</section>

{{-- Main Content --}}
<main class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {{-- Left Content --}}
        <div class="lg:col-span-2 space-y-8">
{{-- Berita Terbaru Section - Mobile Friendly Version --}}
@if($latestNews->count() > 0)
<section>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl lg:text-2xl font-bold text-gray-900 flex items-center">
            <div class="w-1 h-8 bg-red-600 rounded-full mr-3"></div>
            Berita Terbaru
        </h2>
    </div>

    <div class="space-y-4">
        @foreach($latestNews as $news)
        <div class="news-card">
            <div class="flex items-center min-h-[120px]">
                {{-- Thumbnail - Responsive sizing but always on the left --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('news.show', $news->slug) }}">
                        <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                             alt="{{ $news->judul }}" 
                             class="h-28 w-40 rounded-md object-cover">
                    </a>
                </div>
                
                {{-- Content - Always on the right, flexible width --}}
                <div class="flex-1 p-3 md:p-4">
                    <h3 class="font-semibold mobile-title md:text-base lg:text-lg leading-tight text-gray-900 mb-2 hover:text-red-600 transition-colors line-clamp-2">
                        <a href="{{ route('news.show', $news->slug) }}">{{ $news->judul}}</a>
                    </h3>
                    
                    @if($news->excerpt)
                    <p class="text-gray-600 mobile-excerpt md:text-sm mb-3 line-clamp-2">
                        {{ Str::limit($news->excerpt, 100) }}
                    </p>
                    @endif
                    
                    <div class="flex items-center mobile-meta md:text-xs text-gray-500">
                        <span class="font-semibold mr-2" style="color: {{ $news->category->color }};">
                            {{ $news->category->nama_kategori }}
                        </span>
                        <span>•</span>
                        <span class="ml-2">
                            {{ $news->published_at ? $news->published_at->format('d M Y') : $news->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
{{-- Video Terbaru Section - Menggunakan komponen terpisah --}}
@include('components.latest-videos', ['latestVideos' => $latestVideos])
            

{{-- Berita Lainnya Section - Mobile Friendly Version --}}
{{-- Berita Lainnya Section - Mobile Friendly Version --}}
@if($otherNews->count() > 0)
<section class="mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl lg:text-2xl font-bold text-gray-900 flex items-center">
            <div class="w-1 h-8 bg-blue-600 rounded-full mr-3"></div>
            Jangan Lewatkan
        </h2>
    </div>

    <div class="space-y-4" id="other-news-container">
        @foreach($otherNews as $news)
        <div class="featured-card">
            <div class="flex items-center min-h-[120px]">
                {{-- Thumbnail - Responsive sizing but always on the left --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('news.show', $news->slug) }}">
                        <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                             alt="{{ $news->judul }}" 
                             class="w-28 h-20 md:w-40 md:h-28 lg:w-48 lg:h-32 object-cover">
                    </a>
                </div>
                
                {{-- Content - Always on the right, flexible width --}}
                <div class="flex-1 p-3 md:p-4">
                    <h3 class="font-semibold mobile-title md:text-base lg:text-lg leading-tight text-gray-900 mb-2 hover:text-red-600 transition-colors line-clamp-2">
                        <a href="{{ route('news.show', $news->slug) }}">{{ $news->judul }}</a>
                    </h3>
                    
                    <p class="text-gray-600 mobile-excerpt md:text-sm mb-3 line-clamp-2">
                        {{ $news->excerpt }}
                    </p>
                    
                    <div class="flex items-center mobile-meta md:text-xs text-gray-500">
                        <span class="font-semibold mr-2" style="color: {{ $news->category->color }};">
                            {{ $news->category->nama_kategori }}
                        </span>
                        <span>•</span>
                        <span class="ml-2">
                            {{ $news->published_at ? $news->published_at->format('d M Y') : $news->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
        {{-- Tombol Load More --}}
    <div class="text-center mt-8">
        <button id="load-more-btn" 
                class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:bg-gray-400 text-sm">
            Muat Lebih Banyak
        </button>
    </div>
</section>
@endif
        </div>

        {{-- Right Sidebar --}}
        <div class="lg:col-span-1">

                            {{-- KOTAK ABU-ABU BARU --}}
            <div class="bg-gray-200 rounded-lg text-center mb-6 px-8 py-20">
                <p class="text-sm text-gray-600">Advertisement</p>
                <h4 class="text-base font-semibold text-gray-700">Pasang Iklan Anda Disini</h4>
                <p class="text-sm text-gray-500">marketing@efarinatv.net</p>
            </div>

            
            {{-- Berita Populer --}}
            @if($popularNews->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-fire text-orange-500 mr-2"></i>
                    Berita Populer
                </h3>
                <div class="space-y-3">
                    @foreach($popularNews->take(8) as $popular)
                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                        <img src="{{ $popular->thumbnail ? asset('storage/' . $popular->thumbnail) : 'https://via.placeholder.com/80x60' }}" 
                             alt="{{ $popular->judul }}" 
                             class="w-16 h-12 object-cover rounded flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 text-sm leading-tight mb-1 hover:text-red-600 transition-colors line-clamp-2">
                                <a href="{{ route('news.show', $popular->slug) }}">{{ Str::limit($popular->judul, 80) }}</a>
                            </h4>
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-eye mr-1"></i>
                                {{ number_format($popular->views_count) }}
                                <span class="mx-2">•</span>
                                <span>{{ $popular->published_at ? $popular->published_at->format('d M') : $popular->created_at->format('d M') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            {{-- Video Unggulan Widget - Menggunakan komponen terpisah --}}
            @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])

        </div>
    </div>
</main>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loadMoreBtn = document.getElementById('load-more-btn');
        const container = document.getElementById('other-news-container');
        let page = 2; // Halaman selanjutnya yang akan dimuat

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                // Tampilkan loading text dan nonaktifkan tombol
                loadMoreBtn.textContent = 'Memuat...';
                loadMoreBtn.disabled = true;

                fetch('{{ route("news.load-more") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ page: page })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.html.trim() !== '') {
                        // Tambahkan berita baru ke kontainer
                        container.insertAdjacentHTML('beforeend', data.html);
                        page++; // Naikkan nomor halaman untuk permintaan berikutnya
                        
                        // Kembalikan tombol ke keadaan semula
                        loadMoreBtn.textContent = 'Muat Lebih Banyak';
                        loadMoreBtn.disabled = false;
                    } else {
                        // Jika tidak ada berita lagi, sembunyikan tombol
                        loadMoreBtn.textContent = 'Tidak Ada Berita Lagi';
                        loadMoreBtn.disabled = true;
                        setTimeout(() => {
                           loadMoreBtn.style.display = 'none';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadMoreBtn.textContent = 'Gagal Memuat';
                    loadMoreBtn.disabled = false;
                });
            });
        }
    });
</script>
@endpush
@endsection