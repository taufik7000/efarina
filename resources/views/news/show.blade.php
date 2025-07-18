@extends('layouts.app')

@section('title', $news->judul . ' - Portal Berita')

@section('content')
<div class="bg-gray-50 min-h-screen">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
                <span class="mx-2">/</span>
                <a href="{{ route('news.index') }}" class="hover:text-blue-600">Berita</a>
                <span class="mx-2">/</span>
                <a href="{{ route('news.category', $news->category->slug) }}" class="hover:text-blue-600">{{ $news->category->nama_kategori }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">{{ Str::limit($news->judul, 50) }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Article --}}
            <div class="lg:col-span-3">
                <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                    {{-- Article Header --}}
                    <div class="p-6 border-b">
                        {{-- Category & Featured Badge --}}
                        <div class="flex items-center justify-between mb-4">
                            <a href="{{ route('news.category', $news->category->slug) }}" 
                               class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-700 transition-colors">
                                {{ $news->category->nama_kategori }}
                            </a>
                            @if($news->is_featured)
                            <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-star"></i> Unggulan
                            </span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4 leading-tight">{{ $news->judul }}</h1>

                        {{-- Summary --}}
                        @if($news->ringkasan)
                        <p class="text-xl text-gray-600 mb-6 leading-relaxed">{{ $news->ringkasan }}</p>
                        @endif

                        {{-- Article Meta --}}
                        <div class="flex flex-wrap items-center justify-between text-sm text-gray-500 border-t border-b py-4 my-4">
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2"></i>
                                    <span>{{ $news->author->name }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>{{ $news->created_at->format('d F Y, H:i') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-eye mr-2"></i>
                                    <span id="view-count">{{ number_format($news->views_count) }} views</span>
                                </div>
                            </div>

                            {{-- Share Buttons --}}
                            <div class="flex items-center space-x-3 mt-2 lg:mt-0">
                                <span class="text-gray-700 font-medium">Bagikan:</span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                                   target="_blank" 
                                   class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-colors">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($news->judul) }}" 
                                   target="_blank" 
                                   class="bg-sky-500 text-white p-2 rounded-full hover:bg-sky-600 transition-colors">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($news->judul . ' - ' . request()->url()) }}" 
                                   target="_blank" 
                                   class="bg-green-500 text-white p-2 rounded-full hover:bg-green-600 transition-colors">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <button onclick="copyToClipboard()" 
                                        class="bg-gray-500 text-white p-2 rounded-full hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Featured Image --}}
                    @if($news->thumbnail)
                    <div class="px-6">
                        <img src="{{ asset('storage/' . $news->thumbnail) }}" 
                             alt="{{ $news->judul }}" 
                             class="w-full h-64 lg:h-96 object-cover rounded-lg">
                    </div>
                    @endif

                    {{-- Article Content --}}
                    <div class="p-6">
                        <div class="prose prose-lg max-w-none">
                            {!! nl2br(e($news->konten)) !!}
                        </div>

                        {{-- Tags --}}
                        @if($news->tags->count() > 0)
                        <div class="mt-8 pt-6 border-t">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Tags:</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($news->tags as $tag)
                                <a href="{{ route('news.tag', $tag->slug) }}" 
                                   class="bg-blue-100 text-blue-800 px-3 py-2 rounded-full text-sm hover:bg-blue-200 transition-colors">
                                    #{{ $tag->nama_tag }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </article>

                {{-- Related News --}}
                @if($relatedNews->count() > 0)
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Berita Terkait</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($relatedNews as $related)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="flex">
                                <img src="{{ $related->thumbnail ? asset('storage/' . $related->thumbnail) : 'https://via.placeholder.com/120x80' }}" 
                                     alt="{{ $related->judul }}" 
                                     class="w-32 h-24 object-cover">
                                <div class="p-4 flex-1">
                                    <h3 class="font-semibold text-gray-900 hover:text-blue-600 transition-colors mb-2">
                                        <a href="{{ route('news.show', $related->slug) }}">{{ Str::limit($related->judul, 80) }}</a>
                                    </h3>
                                    <div class="flex items-center text-xs text-gray-500">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2">
                                            {{ $related->category->nama_kategori }}
                                        </span>
                                        <span>{{ $related->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Popular News --}}
                @if($popularNews->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Populer</h3>
                    <div class="space-y-4">
                        @foreach($popularNews as $index => $popular)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('news.show', $popular->slug) }}">{{ Str::limit($popular->judul, 60) }}</a>
                                </h4>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2">
                                        {{ $popular->category->nama_kategori }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ number_format($popular->views_count) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Categories --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kategori</h3>
                    <div class="space-y-2">
                        @php
                        $categories = \App\Models\NewsCategory::active()
                            ->ordered()
                            ->withCount(['news' => function ($query) {
                                $query->where('status', 'published');
                            }])
                            ->get();
                        @endphp
                        
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

                {{-- Recent News --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Terbaru</h3>
                    <div class="space-y-4">
                        @php
                        $recentNews = \App\Models\News::with(['category'])
                            ->where('status', 'published')
                            ->where('id', '!=', $news->id)
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

@push('scripts')
<script>
    function copyToClipboard() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            // Show success message
            alert('Link berhasil disalin!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>
@endpush