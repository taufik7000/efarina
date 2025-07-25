@extends('layouts.app')

@section('title', 'Kategori: ' . $category->nama_kategori . ' - Portal Berita')

@section('content')
<div class="bg-gray-50 min-h-screen">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background-color: {{ $category->color }}">
                    <i class="fas fa-folder text-2xl"></i>
                </div>
                <h1 class="text-4xl font-bold mb-2">{{ $category->nama_kategori }}</h1>
                @if($category->deskripsi)
                <p class="text-xl text-blue-100 mb-4">{{ $category->deskripsi }}</p>
                @endif
                <p class="text-blue-200">{{ $news->total() }} berita ditemukan</p>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
                <span class="mx-2">/</span>
                <a href="{{ route('news.index') }}" class="hover:text-blue-600">Berita</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">{{ $category->nama_kategori }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Search Bar --}}
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" action="{{ route('news.category', $category->slug) }}">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <div class="relative">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Cari berita dalam kategori {{ $category->nama_kategori }}..." 
                                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>
                                Cari
                            </button>
                            
                            @if(request('search'))
                            <a href="{{ route('news.category', $category->slug) }}" class="text-gray-600 hover:text-gray-800 transition-colors">
                                <i class="fas fa-times mr-1"></i>
                                Reset
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
                        dalam kategori <strong>{{ $category->nama_kategori }}</strong>
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
                                <span class="text-white px-3 py-1 rounded-full text-sm font-medium" style="background-color: {{ $category->color }}">
                                    {{ $category->nama_kategori }}
                                </span>
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
                    <i class="fas fa-folder-open text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Berita Ditemukan</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request('search'))
                        Tidak ada berita yang cocok dengan pencarian "{{ request('search') }}" dalam kategori {{ $category->nama_kategori }}.
                        @else
                        Belum ada berita dalam kategori {{ $category->nama_kategori }} saat ini.
                        @endif
                    </p>
                    <div class="space-x-4">
                        <a href="{{ route('news.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-newspaper mr-2"></i>
                            Lihat Semua Berita
                        </a>
                        @if(request('search'))
                        <a href="{{ route('news.category', $category->slug) }}" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Kategori
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Category Info --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background-color: {{ $category->color }}">
                            <i class="fas fa-folder text-2xl text-white"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $category->nama_kategori }}</h3>
                        @if($category->deskripsi)
                        <p class="text-gray-600 text-sm mb-4">{{ $category->deskripsi }}</p>
                        @endif
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-blue-800 font-semibold">{{ $news->total() }} Berita</p>
                            <p class="text-blue-600 text-sm">dalam kategori ini</p>
                        </div>
                    </div>
                </div>

                {{-- Other Categories --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kategori Lainnya</h3>
                    <div class="space-y-2">
                        @php
                        $otherCategories = \App\Models\NewsCategory::active()
                            ->where('id', '!=', $category->id)
                            ->ordered()
                            ->withCount(['news' => function ($query) {
                                $query->where('status', 'published');
                            }])
                            ->get();
                        @endphp
                        
                        @foreach($otherCategories as $otherCategory)
                        <a href="{{ route('news.category', $otherCategory->slug) }}" 
                           class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $otherCategory->color }}"></div>
                                <span class="text-gray-700 group-hover:text-blue-600">{{ $otherCategory->nama_kategori }}</span>
                            </div>
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $otherCategory->news_count }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Popular in Category --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Populer di {{ $category->nama_kategori }}</h3>
                    <div class="space-y-4">
                        @php
                        $popularInCategory = \App\Models\News::with(['author'])
                            ->where('status', 'published')
                            ->where('news_category_id', $category->id)
                            ->orderBy('views_count', 'desc')
                            ->limit(5)
                            ->get();
                        @endphp
                        
                        @foreach($popularInCategory as $index => $popular)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color: {{ $category->color }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $popular->slug) }}">{{ Str::limit($popular->judul, 60) }}</a>
                                </h4>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span class="flex items-center mr-3">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ number_format($popular->views_count) }}
                                    </span>
                                    <span>{{ $popular->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Recent News --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Terbaru</h3>
                    <div class="space-y-4">
                        @php
                        $recentNews = \App\Models\News::with(['category'])
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
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span class="px-2 py-1 rounded text-xs mr-2" style="background-color: {{ $recent->category->color }}20; color: {{ $recent->category->color }}">
                                        {{ $recent->category->nama_kategori }}
                                    </span>
                                    <span>{{ $recent->created_at->format('d M Y') }}</span>
                                </div>
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