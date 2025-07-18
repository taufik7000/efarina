@extends('layouts.app')

@section('title', 'Home - Portal Berita')

@section('content')
<div class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    {{-- Hero Section with Featured News --}}
    <section class="py-8 px-4">
        <div class="max-w-7xl mx-auto">
            {{-- Hero Banner --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-blue-950 text-white shadow-2xl mb-16">
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.3),rgba(255,255,255,0))] opacity-70"></div>
                <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 32 32\" width=\"32\" height=\"32\" fill=\"none\" stroke=\"white\"><path d=\"M0 .5H31.5V32\"/></svg>'); background-size: 32px 32px;"></div>
                
                <div class="relative z-10 p-8 md:p-12">
                    <div class="text-center">
                        <!-- Icon with Glassmorphism -->
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl mb-8 shadow-xl">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                            </svg>
                        </div>
                        
                        <!-- Main Title with Gradient Text -->
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-4 bg-gradient-to-b from-white to-slate-300 bg-clip-text text-transparent">
                            Portal Berita Terkini
                        </h1>
                        
                        <p class="text-lg md:text-xl text-slate-300 mb-12 max-w-3xl mx-auto leading-relaxed">
                            Dapatkan informasi terbaru dan terpercaya dari sumber yang dapat dipercaya
                        </p>
                        
                        <!-- Search Bar -->
                        <div class="max-w-2xl mx-auto">
                            <div class="relative">
                                <input type="text" 
                                       placeholder="Cari berita terbaru..." 
                                       class="w-full px-6 py-4 pl-14 pr-16 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-white/70 focus:ring-2 focus:ring-white/50 focus:border-transparent outline-none transition-all">
                                <svg class="absolute left-5 top-1/2 transform -translate-y-1/2 w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <button class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-white text-blue-600 px-4 py-2 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Featured News Section --}}
            @if($featuredNews->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
                {{-- Main Featured News --}}
                <div class="lg:col-span-2">
                    @php $mainNews = $featuredNews->first(); @endphp
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-100">
                        <div class="relative">
                            <img src="{{ $mainNews->thumbnail ? asset('storage/' . $mainNews->thumbnail) : 'https://via.placeholder.com/800x400' }}" 
                                 alt="{{ $mainNews->judul }}" 
                                 class="w-full h-64 lg:h-80 object-cover">
                            <div class="absolute top-4 left-4">
                                <span class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg">
                                    {{ $mainNews->category->nama_kategori }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-3 py-2 rounded-full text-xs font-semibold shadow-lg">
                                    <i class="fas fa-star mr-1"></i> Unggulan
                                </span>
                            </div>
                        </div>
                        <div class="p-8">
                            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4 hover:text-blue-600 transition-colors leading-tight">
                                <a href="{{ route('news.show', $mainNews->slug) }}">{{ $mainNews->judul }}</a>
                            </h2>
                            <p class="text-gray-600 mb-6 line-clamp-3 text-lg leading-relaxed">{{ Str::limit($mainNews->ringkasan, 150) }}</p>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-user mr-2 text-blue-500"></i>
                                        {{ $mainNews->author->name }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-blue-500"></i>
                                        {{ $mainNews->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <span class="flex items-center text-blue-600 font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    {{ number_format($mainNews->views_count ?? 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Secondary Featured News --}}
                <div class="space-y-6">
                    @foreach($featuredNews->skip(1)->take(3) as $news)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100">
                        <div class="flex">
                            <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/150x100' }}" 
                                 alt="{{ $news->judul }}" 
                                 class="w-28 h-24 object-cover">
                            <div class="p-4 flex-1">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $news->category->nama_kategori }}
                                </span>
                                <h3 class="text-sm font-semibold text-gray-900 mt-2 mb-2 hover:text-blue-600 transition-colors leading-snug">
                                    <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 60) }}</a>
                                </h3>
                                <p class="text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $news->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Latest News Section --}}
    <section class="py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Berita Terbaru</h2>
                    <div class="w-16 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"></div>
                </div>
                <a href="{{ route('news.index') }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                    Lihat Semua →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latestNews as $news)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                    <div class="relative">
                        <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                             alt="{{ $news->judul }}" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-3 left-3">
                            <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg">
                                {{ $news->category->nama_kategori }}
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-300"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 hover:text-blue-600 transition-colors leading-tight">
                            <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 70) }}</a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">{{ Str::limit($news->ringkasan, 100) }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center space-x-3">
                                <span class="flex items-center">
                                    <i class="fas fa-user mr-1 text-blue-500"></i>
                                    {{ $news->author->name }}
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-calendar mr-1 text-blue-500"></i>
                                    {{ $news->created_at->format('d M Y') }}
                                </span>
                            </div>
                            <span class="flex items-center text-blue-600 font-medium">
                                <i class="fas fa-eye mr-1"></i>
                                {{ number_format($news->views_count ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Popular News & Categories Sidebar --}}
    <section class="py-12 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Popular News --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mr-4">Berita Populer</h2>
                        <div class="w-12 h-1 bg-gradient-to-r from-orange-500 to-red-500 rounded-full"></div>
                    </div>
                    <div class="space-y-4">
                        @foreach($popularNews as $index => $news)
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors border border-gray-100 hover:border-blue-200 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4 shadow-lg">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 hover:text-blue-600 transition-colors group-hover:text-blue-600">
                                    <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 80) }}</a>
                                </h3>
                                <div class="flex items-center text-sm text-gray-500 mt-2 space-x-4">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $news->category->nama_kategori }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-eye mr-1 text-blue-500"></i>
                                        {{ number_format($news->views_count ?? 0) }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $news->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Categories --}}
                <div>
                    <div class="flex items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mr-4">Kategori</h2>
                        <div class="w-8 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"></div>
                    </div>
                    <div class="space-y-3">
                        @foreach($categories as $category)
                        <a href="{{ route('news.category', $category->slug) }}" 
                           class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition-all duration-300 group border border-gray-100 hover:border-blue-200 hover:shadow-md">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-3 shadow-sm" style="background-color: {{ $category->color }}"></div>
                                <span class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                                    {{ $category->nama_kategori }}
                                </span>
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                {{ $category->news_count }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                    
                    {{-- View All Categories --}}
                    <div class="mt-6">
                        <a href="{{ route('news.index') }}" 
                           class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-4 rounded-xl font-medium text-center block hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                            Jelajahi Semua Kategori
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Newsletter Section --}}
    <section class="py-16 px-4 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.3),rgba(255,255,255,0))] opacity-70"></div>
        
        <div class="relative max-w-4xl mx-auto text-center text-white">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl mb-8 shadow-xl">
                <i class="fas fa-envelope text-2xl text-white"></i>
            </div>
            
            <h2 class="text-3xl md:text-4xl font-bold mb-4 bg-gradient-to-b from-white to-slate-300 bg-clip-text text-transparent">
                Berlangganan Newsletter
            </h2>
            
            <p class="text-lg text-slate-300 mb-8 max-w-2xl mx-auto">
                Dapatkan berita terbaru dan update langsung ke email Anda
            </p>
            
            <form class="max-w-lg mx-auto">
                <div class="flex flex-col md:flex-row gap-4">
                    <input type="email" 
                           placeholder="Masukkan email Anda" 
                           class="flex-1 px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-white/70 focus:ring-2 focus:ring-white/50 focus:border-transparent outline-none transition-all">
                    <button type="submit" 
                            class="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition-colors shadow-lg hover:shadow-xl">
                        Berlangganan
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Smooth transitions for all interactive elements */
    * {
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    /* Enhanced hover effects */
    .group:hover .group-hover\:text-blue-600 {
        color: #2563eb;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Professional focus states */
    input:focus, button:focus, a:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .text-4xl {
            font-size: 2rem;
        }
        
        .text-3xl {
            font-size: 1.875rem;
        }
        
        .py-16 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }
    }
</style>
@endpush