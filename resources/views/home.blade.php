{{-- Updated home.blade.php dengan Hero Section + Video Unggulan di Sidebar --}}
@extends('layouts.app')

@section('title', 'Portal Berita Timnas Indonesia')

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
    max-width: 1100px;
}

.hero-section {
    margin-top: 180px;
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
</style>
@endpush

@section('content')
{{-- Hero Section dengan Berita Unggulan --}}
<section class="hero-section">
    <div class="container mx-auto px-4">
        @if($featuredNews->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Featured News --}}
            <div class="lg:col-span-2">
                @php $mainFeatured = $featuredNews->first(); @endphp
                <div class="featured-card h-full">
                    <div class="relative">
                        <img src="{{ $mainFeatured->thumbnail ? asset('storage/' . $mainFeatured->thumbnail) : 'https://via.placeholder.com/800x450' }}" 
                             alt="{{ $mainFeatured->judul }}" 
                             class="w-full h-80 lg:h-96 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <span class="category-badge absolute top-4 left-4" style="background-color: {{ $mainFeatured->category->color }}; color: white;">
                            {{ $mainFeatured->category->nama_kategori }}
                        </span>
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                            <h1 class="text-xl lg:text-3xl font-bold mb-2 leading-tight">
                                <a href="{{ route('news.show', $mainFeatured->slug) }}" class="hover:text-red-300 transition-colors">
                                    {{ $mainFeatured->judul }}
                                </a>
                            </h1>
                            @if($mainFeatured->excerpt)
                            <p class="text-gray-200 mb-3 text-sm lg:text-base line-clamp-2">{{ Str::limit($mainFeatured->excerpt, 150) }}</p>
                            @endif
                            <div class="flex items-center text-sm text-gray-300">
                                <i class="fas fa-clock mr-2"></i>
                                {{ $mainFeatured->published_at ? $mainFeatured->published_at->format('d M Y, H:i') : $mainFeatured->created_at->format('d M Y, H:i') }}
                                <span class="mx-3">•</span>
                                <i class="fas fa-eye mr-1"></i>
                                {{ number_format($mainFeatured->views_count) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary Featured News --}}
            <div class="space-y-4">
                @foreach($featuredNews->skip(1)->take(3) as $news)
                <div class="featured-card">
                    <div class="flex">
                        <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/200x120' }}" 
                             alt="{{ $news->judul }}" 
                             class="w-24 h-20 object-cover rounded-l-lg">
                        <div class="flex-1 p-3">
                            <span class="category-badge text-xs mb-1" style="background-color: {{ $news->category->color }}; color: white;">
                                {{ $news->category->nama_kategori }}
                            </span>
                            <h3 class="font-semibold text-gray-900 text-sm leading-tight mb-1 hover:text-red-600 transition-colors">
                                <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 80) }}</a>
                            </h3>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $news->published_at ? $news->published_at->format('d M') : $news->created_at->format('d M') }}
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

{{-- Main Content --}}
<main class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left Content --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Berita Terbaru Section --}}
            @if($latestNews->count() > 0)
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-900 flex items-center">
                        <div class="w-1 h-8 bg-red-600 rounded-full mr-3"></div>
                        Berita Terbaru
                    </h2>
                    <a href="{{ route('news.index') }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($latestNews as $news)
                    <div class="news-card">
                        <div class="relative">
                            <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                                 alt="{{ $news->judul }}" 
                                 class="w-full h-48 object-cover">
                            <span class="category-badge absolute top-3 left-3" style="background-color: {{ $news->category->color }}; color: white;">
                                {{ $news->category->nama_kategori }}
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 leading-tight hover:text-red-600 transition-colors">
                                <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 80) }}</a>
                            </h3>
                            @if($news->excerpt)
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ Str::limit($news->excerpt, 100) }}</p>
                            @endif
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $news->published_at ? $news->published_at->format('d M Y') : $news->created_at->format('d M Y') }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    {{ number_format($news->views_count) }}
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

        </div>

        {{-- Right Sidebar --}}
        <div class="lg:col-span-1">
            
            {{-- Berita Populer --}}
            @if($popularNews->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-fire text-orange-500 mr-2"></i>
                    Berita Populer
                </h3>
                <div class="space-y-3">
                    @foreach($popularNews->take(5) as $popular)
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

            {{-- Kategori Berita --}}
            @if($newsCategories->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-list text-blue-500 mr-2"></i>
                    Kategori Berita
                </h3>
                <div class="space-y-1">
                    @foreach($newsCategories as $category)
                    <a href="{{ route('news.category', $category->slug) }}" 
                       class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                            <span class="text-gray-700 group-hover:text-blue-600 transition-colors">{{ $category->nama_kategori }}</span>
                        </div>
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">
                            {{ $category->news_count }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Video Unggulan Widget - Menggunakan komponen terpisah --}}
            @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])

        </div>
    </div>
</main>
@endsection