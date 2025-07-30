@foreach($newsItems as $news)
<div class="featured-card">
    <div class="flex items-center min-h-[120px]">
        {{-- Thumbnail - Ukuran responsif dan selalu di kiri --}}
        <div class="flex-shrink-0">
            <a href="{{ route('news.show', $news->slug) }}">
                <img src="{{ $news->thumbnail ? asset('storage/' . $news->thumbnail) : 'https://via.placeholder.com/400x250' }}" 
                     alt="{{ $news->judul }}" 
                     class="w-28 h-20 md:w-40 md:h-28 lg:w-48 lg:h-32 object-cover">
            </a>
        </div>
        
        {{-- Konten - Selalu di kanan dengan lebar fleksibel --}}
        <div class="flex-1 p-3 md:p-4">
            <h3 class="font-semibold mobile-title md:text-base lg:text-lg leading-tight text-gray-900 mb-2 hover:text-red-600 transition-colors">
                <a href="{{ route('news.show', $news->slug) }}">{{ $news->judul }}</a>
            </h3>
            
            @if($news->excerpt)
            <p class="text-gray-600 mobile-excerpt md:text-sm mb-3 line-clamp-2">
                {{ Str::limit($news->excerpt, 100) }}
            </p>
            @endif
            
            <div class="flex items-center mobile-meta md:text-xs text-gray-500">
                <span class="font-semibold mr-2" style="background-color: {{ $news->category->color }}; color: white; padding: 2px 8px; border-radius: 999px; font-size: 0.65rem;">
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