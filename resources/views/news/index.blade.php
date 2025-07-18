@extends('layouts.app')

@section('title', 'Semua Berita - Portal Berita')

@section('content')
<div class="bg-gray-50 min-h-screen">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Semua Berita</h1>
                <p class="text-xl text-blue-100">Temukan berita terbaru dan terpercaya</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Search & Filter Bar --}}
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" action="{{ route('news.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Search Input --}}
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Berita</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="search"
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Masukkan kata kunci..." 
                                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>

                            {{-- Category Filter --}}
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select name="category" id="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori }} ({{ $cat->news_count }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>
                                Cari Berita
                            </button>
                            
                            @if(request()->hasAny(['search', 'category']))
                            <a href="{{ route('news.index') }}" class="text-gray-600 hover:text-gray-800 transition-colors">
                                <i class="fas fa-times mr-1"></i>
                                Reset Filter
                            </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Results Info --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="text-gray-600">
                        Menampilkan {{ $news->firstItem() ?? 0 }} - {{ $news->lastItem() ?? 0 }} dari {{ $news->total() }} berita
                        @if(request('search'))
                        untuk "<strong>{{ request('search') }}</strong>"
                        @endif
                    </div>
                </div>

                {{-- News Grid --}}
                @if($news->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    @foreach($news as $article)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="relative">
                            <img src="{{ $article->thumbnail ? asset('storage/' . $article->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                                 alt="{{ $article->judul }}" 
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-3 left-3">
                                <a href="{{ route('news.category', $article->category->slug) }}" 
                                   class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-700 transition-colors">
                                    {{ $article->category->nama_kategori }}
                                </a>
                            </div>
                            @if($article->is_featured)
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-star"></i> Unggulan
                                </span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
                                <a href="{{ route('news.show', $article->slug) }}">{{ $article->judul }}</a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ Str::limit($article->ringkasan, 120) }}</p>
                            
                            {{-- Tags --}}
                            @if($article->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($article->tags->take(3) as $tag)
                                <a href="{{ route('news.tag', $tag->slug) }}" 
                                   class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                    #{{ $tag->nama_tag }}
                                </a>
                                @endforeach
                            </div>
                            @endif
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2"></i>
                                    {{ $article->author->name }}
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ number_format($article->views_count) }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $article->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="flex justify-center">
                    {{ $news->appends(request()->query())->links() }}
                </div>
                @else
                {{-- No Results --}}
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-newspaper text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Berita Ditemukan</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request('search'))
                        Tidak ada berita yang cocok dengan pencarian "{{ request('search') }}".
                        @else
                        Belum ada berita yang tersedia saat ini.
                        @endif
                    </p>
                    <a href="{{ route('news.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Lihat Semua Berita
                    </a>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Categories Widget --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kategori Berita</h3>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                        <a href="{{ route('news.category', $category->slug) }}" 
                           class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                                <span class="text-gray-700 group-hover:text-blue-600">{{ $category->nama_kategori }}</span>
                            </div>
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $category->news_count }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Recent News Widget --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Terbaru</h3>
                    <div class="space-y-4">
                        @php
                        $recentNews = \App\Models\News::with(['category', 'author'])
                            ->where('status', 'published')
                            ->latest()
                            ->limit(5)
                            ->get();
                        @endphp
                        
                        @foreach($recentNews as $recent)
                        <div class="flex items-start space-x-3">
                            <img src="{{ $recent->thumbnail ? asset('storage/' . $recent->thumbnail) : 'https://via.placeholder.com/60x60' }}" 
                                 alt="{{ $recent->judul }}" 
                                 class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $recent->slug) }}">{{ Str::limit($recent->judul, 60) }}</a>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $recent->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush