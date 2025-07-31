@extends('layouts.app')

@section('title', 'Tayangan Video Terbaru - Efarina TV')

@push('styles')
<style>
/* Modern Video Portal Styles */
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

.hero-section {

    position: relative;
    overflow: hidden;
    padding: 2rem 0 3rem 0;
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


/* Hero Section Title */
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

/* Video Cards in Hero */
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

/* Hero section breadcrumb styling */
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
        margin-top: 120px;
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

/* Professional focus states */
input:focus, select:focus, button:focus, a:focus {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
@endpush

@section('content')
<div class="bg-blue-950 header-nav-blue">
    {{-- Hero Section dengan Video Unggulan --}}
    <section class="hero-section">
        
        <div class="container mx-auto px-4 relative mt-28">
            @if($featuredVideos->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                
                {{-- Main Featured Video --}}
                <div class="lg:col-span-2">
                    @php $mainFeatured = $featuredVideos->first(); @endphp
                    <div class="featured-video-card h-full relative">
                        <img src="{{ $mainFeatured->thumbnail_maxres ?? $mainFeatured->thumbnail_hq ?? 'https://via.placeholder.com/800x450' }}" 
                             alt="{{ $mainFeatured->title }}" 
                             class="w-full h-full object-cover rounded-lg">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/70 to-transparent rounded-lg"></div>
                        
                        {{-- Play Button --}}
                        <a href="{{ route('video.show', $mainFeatured->video_id) }}" class="play-button">
                            <i class="fas fa-play"></i>
                        </a>
                        
                        {{-- Duration Badge --}}
                        @if($mainFeatured->duration_seconds)
                        <div class="duration-badge">
                            {{ gmdate($mainFeatured->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $mainFeatured->duration_seconds) }}
                        </div>
                        @endif
                        
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                            @if($mainFeatured->category)
                            <span class="category-badge mb-2" style="background-color: {{ $mainFeatured->category->color }}; color: white;">
                                {{ $mainFeatured->category->nama_kategori }}
                            </span>
                            @endif
                            <h1 class="text-xl lg:text-3xl font-bold mb-2 leading-tight">
                                <a href="{{ route('video.show', $mainFeatured->video_id) }}" class="hover:text-red-300 transition-colors">
                                    {{ $mainFeatured->title }}
                                </a>
                            </h1>
                            <div class="flex items-center text-sm text-gray-300">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ $mainFeatured->published_at ? $mainFeatured->published_at->format('d M Y') : 'N/A' }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-eye mr-2"></i>
                                {{ number_format($mainFeatured->view_count ?? 0) }} views
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Video Unggulan --}}
                <div class="space-y-4">
                    @foreach($featuredVideos->skip(1)->take(3) as $index => $video)
                    <div class="video-card group">
                        <div class="flex">
                            <div class="relative flex-shrink-0 w-32 h-20 lg:w-40 lg:h-24">
                                <img src="{{ $video->thumbnail_hq ?? 'https://via.placeholder.com/300x170' }}" 
                                     alt="{{ $video->title }}" 
                                     class="w-full h-full object-cover">
                                
                                <a href="{{ route('video.show', $video->video_id) }}" class="play-button" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <i class="fas fa-play"></i>
                                </a>
                                
                                @if($video->duration_seconds)
                                <div class="duration-badge text-xs">
                                    {{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $video->duration_seconds) }}
                                </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 p-3">
                                @if($video->category)
                                <span class="category-badge text-xs mb-1" style="background-color: {{ $video->category->color }}; color: white;">
                                    {{ $video->category->nama_kategori }}
                                </span>
                                @endif
                                <h3 class="font-semibold text-sm text-gray-900 mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <a href="{{ route('video.show', $video->video_id) }}">{{ $video->title }}</a>
                                </h3>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-eye mr-1"></i>
                                    {{ number_format($video->view_count ?? 0) }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $video->published_at ? $video->published_at->format('d M') : 'N/A' }}
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Video Grid Component --}}
                @include('videos.components.video-grid', ['videos' => $videos])

                {{-- Pagination Component --}}
                @include('videos.components.pagination', [
                    'videos' => $videos,
                    'paginationInfo' => $paginationInfo
                ])
            </div>

            {{-- Sidebar Component --}}
            @include('videos.components.sidebar', [
                'categories' => $categories,
                'sort' => $sort,
                'paginationInfo' => $paginationInfo
            ])
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when sort or per_page changes
    const sortSelect = document.getElementById('sort');
    const perPageSelect = document.getElementById('per_page');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // Enhanced play button interactions
    const playButtons = document.querySelectorAll('.play-button');
    playButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1)';
        });
    });

    // Lazy loading for better performance
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
});
</script>
@endpush
@endsection