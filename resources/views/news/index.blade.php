@extends('layouts.app')

@section('title', 'Portal Berita')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800">
    {{-- Header --}}
    <nav class="bg-white/95 backdrop-blur-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <div class="flex items-center">
                    <div class="bg-blue-600 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        </svg>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="flex-1 max-w-2xl mx-8">
                    <form action="{{ route('news.index') }}" method="GET" class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search for articles, stories..." 
                               class="w-full pl-4 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Navigation --}}
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600 font-medium">Home</a>
                    <a href="{{ route('news.index') }}" class="text-blue-600 font-medium">Articles</a>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-full font-medium hover:bg-blue-700 transition-colors">
                        Subscribe
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content Area --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Featured Article --}}
                @if($news->first())
                @php $featured = $news->first(); @endphp
                <div class="bg-white rounded-3xl overflow-hidden shadow-xl">
                    <div class="relative">
                        <img src="{{ $featured->thumbnail ? asset('storage/' . $featured->thumbnail) : 'https://via.placeholder.com/800x400' }}" 
                             alt="{{ $featured->judul }}" 
                             class="w-full h-80 object-cover">
                        <div class="absolute top-6 left-6">
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Hot
                            </span>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/32x32" alt="Author" class="w-8 h-8 rounded-full">
                                <span class="text-sm text-gray-600">{{ $featured->author->name ?? 'Admin' }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $featured->created_at->format('M d, Y') }}</span>
                            <span class="text-sm text-gray-500">{{ $featured->reading_time ?? '4' }} min read</span>
                            <span class="text-sm text-gray-500">{{ number_format($featured->views_count ?? 0) }} views</span>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-4 leading-tight">
                            <a href="{{ route('news.show', $featured->slug) }}" class="hover:text-blue-600 transition-colors">
                                {{ $featured->judul }}
                            </a>
                        </h2>
                        <p class="text-gray-600 text-lg leading-relaxed mb-6">
                            {{ Str::limit($featured->excerpt ?? strip_tags($featured->konten), 200) }}
                        </p>
                        <a href="{{ route('news.show', $featured->slug) }}" 
                           class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700 transition-colors">
                            Read more
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @endif

                {{-- All Time Favourites Section --}}
                <div class="bg-white rounded-3xl p-8 shadow-xl">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">All Time Favourites</h3>
                        </div>
                        <button class="text-blue-600 font-semibold hover:text-blue-700">View all</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($news->skip(1)->take(3) as $index => $article)
                        <div class="group cursor-pointer">
                            <div class="relative overflow-hidden rounded-2xl mb-4">
                                <img src="{{ $article->thumbnail ? asset('storage/' . $article->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                                     alt="{{ $article->judul }}" 
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute top-4 right-4">
                                    <div class="bg-black/20 backdrop-blur-sm rounded-full p-2">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 mb-3">
                                <img src="https://via.placeholder.com/32x32" alt="Author" class="w-8 h-8 rounded-full">
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <span>{{ $article->author->name ?? 'Admin' }}</span>
                                    <span>•</span>
                                    <span>{{ $article->created_at->format('M d') }}</span>
                                </div>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                <a href="{{ route('news.show', $article->slug) }}">
                                    {{ Str::limit($article->judul, 60) }}
                                </a>
                            </h4>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>{{ number_format($article->views_count ?? rand(100, 999)) }}</span>
                                <span>👍 {{ rand(10, 99) }}</span>
                                <span>💬 {{ rand(5, 50) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- More Articles Grid --}}
                @if($news->count() > 4)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($news->skip(4)->take(6) as $article)
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="relative">
                            <img src="{{ $article->thumbnail ? asset('storage/' . $article->thumbnail) : 'https://via.placeholder.com/400x200' }}" 
                                 alt="{{ $article->judul }}" 
                                 class="w-full h-40 object-cover">
                            <div class="absolute top-3 left-3">
                                <span class="bg-{{ ['blue', 'green', 'purple', 'orange'][array_rand(['blue', 'green', 'purple', 'orange'])] }}-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $article->category->nama_kategori ?? 'News' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-2 mb-3">
                                <img src="https://via.placeholder.com/24x24" alt="Author" class="w-6 h-6 rounded-full">
                                <span class="text-sm text-gray-500">{{ $article->author->name ?? 'Admin' }}</span>
                                <span class="text-sm text-gray-400">•</span>
                                <span class="text-sm text-gray-500">{{ $article->created_at->format('M d') }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                                <a href="{{ route('news.show', $article->slug) }}">
                                    {{ Str::limit($article->judul, 80) }}
                                </a>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4">
                                {{ Str::limit(strip_tags($article->excerpt ?? $article->konten), 100) }}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 text-xs text-gray-500">
                                    <span>{{ number_format($article->views_count ?? rand(50, 500)) }}</span>
                                    <span>👍 {{ rand(5, 50) }}</span>
                                    <span>💬 {{ rand(2, 25) }}</span>
                                </div>
                                <a href="{{ route('news.show', $article->slug) }}" 
                                   class="text-blue-600 text-sm font-medium hover:text-blue-700">
                                    Read more
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Pagination --}}
                @if($news instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="flex justify-center">
                    <div class="bg-white rounded-full p-2 shadow-lg">
                        {{ $news->links('pagination::tailwind') }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- New Articles --}}
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">New Articles</h3>
                    </div>
                    <div class="space-y-4">
                        @foreach($news->take(3) as $index => $article)
                        <div class="flex space-x-3">
                            <div class="bg-gray-900 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs text-blue-600 font-medium">{{ $article->category->nama_kategori ?? 'News' }}</span>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $article->slug) }}">
                                        {{ Str::limit($article->judul, 50) }}
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-2">
                                    {{ Str::limit(strip_tags($article->excerpt ?? $article->konten), 80) }}
                                </p>
                                <div class="flex items-center space-x-3 text-xs text-gray-400">
                                    <span>{{ number_format($article->views_count ?? rand(10, 100)) }}</span>
                                    <span>👍 {{ rand(1, 20) }}</span>
                                    <span>💬 {{ rand(1, 10) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Categories --}}
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h3 class="font-bold text-gray-900 mb-6">Categories</h3>
                    <div class="space-y-3">
                        @php
                        $categories = \App\Models\NewsCategory::active()
                            ->withCount(['news' => function ($query) {
                                $query->where('status', 'published');
                            }])
                            ->orderBy('news_count', 'desc')
                            ->limit(6)
                            ->get();
                        @endphp
                        
                        @foreach($categories as $category)
                        <a href="{{ route('news.category', $category->slug) }}" 
                           class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $category->color ?? '#6b7280' }}"></div>
                                <span class="font-medium text-gray-900">{{ $category->nama_kategori }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $category->news_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection