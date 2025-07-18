@extends('layouts.app')

@section('title', 'Home - Portal Berita')

@section('content')
<div class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    {{-- Hero Section with Featured News --}}
    <section class="py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Portal Berita Terkini</h1>
                <p class="text-xl text-gray-600">Dapatkan informasi terbaru dan terpercaya</p>
            </div>

            @if($featuredNews->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
                {{-- Main Featured News --}}
                <div class="lg:col-span-2">
                    @php $mainNews = $featuredNews->first(); @endphp
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="relative">
                            <img src="{{ $mainNews->thumbnail ? asset('storage/' . $mainNews->thumbnail) : 'https://via.placeholder.com/800x400' }}" 
                                 alt="{{ $mainNews->judul }}" 
                                 class="w-full h-64 lg:h-80 object-cover">
                            <div class="absolute top-4 left-4">
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $mainNews->category->nama_kategori }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-star"></i> Unggulan
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
                                <a href="{{ route('news.show', $mainNews->slug) }}">{{ $mainNews->judul }}</a>
                            </h2>
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ Str::limit($mainNews->ringkasan, 150) }}</p>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2"></i>
                                    {{ $mainNews->author->name }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    {{ $mainNews->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Secondary Featured News --}}
                <div class="space-y-6">
                    @foreach($featuredNews->skip(1) as $news)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="flex">
                            <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/150x100' }}" 
                                 alt="{{ $news->judul }}" 
                                 class="w-24 h-20 object-cover">
                            <div class="p-4 flex-1">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                    {{ $news->category->nama_kategori }}
                                </span>
                                <h3 class="text-sm font-semibold text-gray-900 mt-2 mb-1 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 60) }}</a>
                                </h3>
                                <p class="text-xs text-gray-500">{{ $news->created_at->format('d M Y') }}</p>
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
                <h2 class="text-3xl font-bold text-gray-900">Berita Terbaru</h2>
                <a href="{{ route('news.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    Lihat Semua →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latestNews as $news)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="relative">
                        <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                             alt="{{ $news->judul }}" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-3 left-3">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">
                                {{ $news->category->nama_kategori }}
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                            <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 70) }}</a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ Str::limit($news->ringkasan, 100) }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                {{ $news->author->name }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-eye mr-1"></i>
                                {{ number_format($news->views_count) }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $news->created_at->format('d M Y') }}
                            </div>
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
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Berita Populer</h2>
                    <div class="space-y-4">
                        @foreach($popularNews as $index => $news)
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $news->slug) }}">{{ Str::limit($news->judul, 80) }}</a>
                                </h3>
                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2">
                                        {{ $news->category->nama_kategori }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ number_format($news->views_count) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Categories --}}
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Kategori</h2>
                    <div class="space-y-3">
                        @foreach($categories as $category)
                        <a href="{{ route('news.category', $category->slug) }}" 
                           class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                                <span class="font-medium text-gray-900 group-hover:text-blue-600">
                                    {{ $category->nama_kategori }}
                                </span>
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $category->news_count }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
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
</style>
@endpush