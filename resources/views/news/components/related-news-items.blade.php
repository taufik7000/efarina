@foreach($newsItems as $item)
{{-- Kartu Berita Individual (Desain List seperti di Home) --}}
<a href="{{ route('news.show', $item->slug) }}" class="group flex gap-4 rounded-lg bg-white p-4 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md hover:border-gray-200">
    
    {{-- Gambar di Kiri --}}
    <div class="flex-shrink-0">
        <img src="{{ $item->thumbnail ? asset('storage/' . $item->thumbnail) : 'https://via.placeholder.com/160x112?text=EfarinaTV' }}"
             alt="Gambar untuk {{ $item->judul }}"
             class="h-28 w-40 rounded-md object-cover">
    </div>

    {{-- Konten Teks di Kanan --}}
    <div class="flex flex-1 flex-col">
        <h3 class="mb-2 text-md font-bold leading-tight text-gray-800 line-clamp-2 group-hover:text-blue-700">
            {{ Str::title(strtolower($item->judul)) }}
        </h3>
        
        {{-- === BAGIAN DESKRIPSI (EXCERPT) === --}}
        <p class="text-sm text-gray-600 line-clamp-2 flex-grow">
            {{ $item->excerpt }}
        </p>
        
        {{-- Metadata Berita --}}
        <div class="mt-3 flex items-center text-xs text-gray-500">
            @if($item->category)
                <span class="font-semibold" style="color: {{ $item->category->color ?? '#be123c' }}">
                    {{ $item->category->nama_kategori }}
                </span>
                <span class="mx-2">•</span>
            @endif
            <span>{{ \Carbon\Carbon::parse($item->published_at ?? $item->created_at)->translatedFormat('d M Y') }}</span>
        </div>
    </div>
</a>
@endforeach

{{-- Pastikan Anda memiliki style ini untuk membatasi teks --}}
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>