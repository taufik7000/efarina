@extends('layouts.app')

@section('title', $news->judul . ' - Portal Berita')

@push('styles')
<style>
/* Clean Design System */
:root {
    --primary: #2563eb;
    --primary-light: #eff6ff;
    --text-primary: #111827;
    --text-secondary: #6b7280;
    --text-light: #9ca3af;
    --border: #e5e7eb;
    --background: #ffffff;
    --background-light: #f9fafb;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.4;
    color: var(--text-primary);
    background: var(--background-light);
}

.container {
    max-width: 1024px;
    margin-top: 150px;
}

.main-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .main-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

/* Breadcrumb */
.breadcrumb {
    background: var(--background);
    padding: 1rem 1rem;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.breadcrumb a {
    color: var(--text-secondary);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: var(--primary);
}

/* Article Styles */
.article-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.category-tag {
    display: inline-block;
    background: var(--primary-light);
    color: var(--primary);
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 1rem;
}

.article-title {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 1rem 0;
    color: var(--text-primary);
}

@media (max-width: 768px) {
    .article-title {
        font-size: 1.875rem;
    }
}

.article-excerpt {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.featured-image {
    width: 100%;
    height: auto;
    margin: 2rem 0;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.article-content {
    font-size: 1rem;
    line-height: 1.5;
    color: var(--text-primary);
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .article-content {
        max-width: none;
        font-size: 1rem;
        line-height: 1.5;
    }
}

.article-content p {
    margin-bottom: 1rem;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4 {
    font-weight: 600;
    color: var(--text-primary);
    margin: 2rem 0 1rem 0;
    line-height: 1.3;
}

.article-content h1 { font-size: 1.875rem; }
.article-content h2 { font-size: 1.625rem; }
.article-content h3 { font-size: 1.375rem; }
.article-content h4 { font-size: 1.125rem; }

.article-content a {
    color: var(--primary);
    text-decoration-color: rgba(37, 99, 235, 0.3);
    text-underline-offset: 2px;
}

.article-content a:hover {
    text-decoration-color: var(--primary);
}

.article-content ul,
.article-content ol {
    margin: 1.5rem 0;
    padding-left: 1.5rem;
}

.article-content blockquote {
    border-left: 3px solid var(--primary);
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: var(--text-secondary);
}

/* Tags */
.article-tags {
    margin: 2rem 0;
    padding: 1.5rem 0;
    border-top: 1px solid var(--border);
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag-item {
    background: var(--background-light);
    color: var(--text-secondary);
    padding: 0.25rem 0.45rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.tag-item:hover {
    background: var(--primary-light);
    color: var(--primary);
}

/* Share Section */
.share-section {
    background: var(--background-light);
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.share-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.share-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: transform 0.2s ease;
}

.share-btn:hover {
    transform: translateY(-1px);
}

.share-btn.facebook { background: #1877f2; color: white; }
.share-btn.twitter { background: #1da1f2; color: white; }
.share-btn.whatsapp { background: #25d366; color: white; }
.share-btn.telegram { background: #0088cc; color: white; }
.share-btn.copy { background: var(--text-secondary); color: white; }


.attachment__caption {
    color: #888888; 
    font-size: 0.875rem;
    margin-bottom: 20px;
    text-decoration: none !important;
}
</style>
@endpush

@section('content')
<div class="container mx-auto mt-20 px-4 py-2">
    
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span> / </span>
        <a href="{{ route('news.index') }}">Berita</a>
        <span> / </span>
        <a href="{{ route('news.category', $news->category->slug) }}">{{ $news->category->nama_kategori }}</a>
        <span> / </span>
        <span>{{ Str::limit($news->judul, 50) }}</span>
    </nav>

    <div class="main-grid">
        {{-- Main Content --}}
        <main class="bg-white rounded-lg shadow-md p-3">
            {{-- Article Header --}}
            <header class="article-header">
                <a href="{{ route('news.category', $news->category->slug) }}" class="category-tag">
                    {{ $news->category->nama_kategori }}
                </a>
                
                <h1 class="article-title">{{ $news->judul }}</h1>
                
                @if($news->excerpt)
                <p class="article-excerpt">{{ $news->excerpt }}</p>
                @endif
                
                <div class="article-meta">
                    @if($news->author)
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span>{{ $news->author->name }}</span>
                    </div>
                    @endif
                    
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                        <span>{{ $news->published_at?->format('d F Y') ?? $news->created_at->format('d F Y') }}</span>
                    </div>
                    
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                        </svg>
                        <span>{{ number_format($news->views_count ?? 0) }} views</span>
                    </div>
                </div>
            </header>

            {{-- Featured Image --}}
            @if($news->thumbnail)
            <img src="{{ asset('storage/' . $news->thumbnail) }}" 
                 alt="{{ $news->judul }}" 
                 class="featured-image">
            @endif

            {{-- Article Content --}}
            <div class="article-content">
                {!! $news->konten !!}
            </div>

            {{-- Livewire Gallery Component --}}
            @livewire('news-gallery', ['news' => $news])

            {{-- Tags --}}
            @if($news->tags && $news->tags->count() > 0)
            <div class="article-tags">
                <div class="text-sm font-medium text-gray-700 mb-2">Tags:</div>
                <div class="tags-list">
                    @foreach($news->tags as $tag)
                    <a href="{{ route('news.tag', $tag->slug) }}" class="tag-item">
                        {{ $tag->nama_tag }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Share Section --}}
            <div class="share-section">
                <div class="text-base font-medium text-gray-900 mb-1">Bagikan Artikel:</div>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                       target="_blank" class="share-btn facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($news->judul) }}" 
                       target="_blank" class="share-btn twitter">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        Twitter
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($news->judul . ' ' . request()->url()) }}" 
                       target="_blank" class="share-btn whatsapp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.63z"/>
                        </svg>
                        WhatsApp
                    </a>
                    <button onclick="copyToClipboard('{{ request()->url() }}')" class="share-btn copy">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Link
                    </button>
                </div>
            </div>

<section class="mt-12 pt-8 border-t border-gray-200">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Berita Terbaru Lainnya</h2>

    {{-- DIUBAH: Kontainer diubah menjadi grid yang responsif --}}
    <div id="related-news-container" class="space-y-4">
        {{-- Kartu berita akan dimuat di sini oleh JavaScript --}}
    </div>

    {{-- Tombol Load More (tidak berubah) --}}
    <div id="load-more-news-wrapper" class="mt-8 text-center ">
        <button id="load-more-news-btn" 
                class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 transition-all"
                data-page="1" 
                data-exclude="{{ $news->id }}">
            <span id="news-btn-text">Muat Lebih Banyak</span>
            <span id="news-btn-loader" class="hidden"><i class="fas fa-spinner fa-spin"></i> Memuat...</span>
        </button>
    </div>

    @include('components.latest-videos', ['latestVideos' => $latestVideos])
</section>
        </main>

        {{-- Sidebar --}}
        <aside>
            @include('news.sidebar', [
                'news' => $news, 
                'relatedNews' => $relatedNews ?? [], 
                'popularNews' => $popularNews ?? []
            ])
            @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Copy to clipboard function
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function showCopySuccess() {
    const btn = event.target.closest('.share-btn');
    if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> Copied!';
        btn.style.background = '#10b981';
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.style.background = '';
        }, 2000);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        console.error('Fallback copy failed: ', err);
        alert('Link berhasil disalin!');
    }
    
    document.body.removeChild(textArea);
}
</script>
<script>
    // Jalankan skrip ini setelah seluruh konten halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        
        // Ambil CSRF token dari tag meta
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // URL API untuk increment view, menggunakan ID berita saat ini
        const url = `{{ route('api.news.view', $news->id) }}`;

        // Kirim permintaan POST ke API menggunakan Fetch API
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
        })
        .catch(error => {
            console.error('Error:', error);
        });

    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const loadMoreBtn = document.getElementById('load-more-news-btn');
    const newsContainer = document.getElementById('related-news-container');
    const loadMoreWrapper = document.getElementById('load-more-news-wrapper');
    const btnText = document.getElementById('news-btn-text');
    const btnLoader = document.getElementById('news-btn-loader');
    
    // Fungsi untuk memuat berita
    function loadRelatedNews() {
        if (!loadMoreBtn) return; // Hentikan jika tombol tidak ada

        let page = parseInt(loadMoreBtn.getAttribute('data-page'));
        const excludeId = loadMoreBtn.getAttribute('data-exclude');
        const categoryId = loadMoreBtn.getAttribute('data-category');

        // Tampilkan loader
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
        loadMoreBtn.disabled = true;

        const url = `{{ route('api.news.related') }}?page=${page}&exclude_id=${excludeId}&category_id=${categoryId}`;

        fetch(url)
            .then(response => response.text())
            .then(html => {
                if (html.trim() !== '') {
                    // Jika ada konten, tambahkan ke kontainer
                    newsContainer.insertAdjacentHTML('beforeend', html);
                    // Naikkan nomor halaman untuk request berikutnya
                    loadMoreBtn.setAttribute('data-page', page + 1);
                } else {
                    // Jika tidak ada konten lagi, sembunyikan tombol
                    if(loadMoreWrapper) loadMoreWrapper.style.display = 'none';
                }
            })
            .catch(error => console.error('Error loading related news:', error))
            .finally(() => {
                // Kembalikan tombol ke keadaan normal
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
                loadMoreBtn.disabled = false;
            });
    }

    // Panggil fungsi untuk memuat berita pertama kali saat halaman dibuka
    loadRelatedNews();

    // Tambahkan event listener untuk klik pada tombol
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadRelatedNews);
    }
});
</script>
@endpush