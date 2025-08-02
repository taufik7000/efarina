@extends('layouts.app')
@section('title', 'Berita Terbaru - Efarina TV')

@push('styles')
<style>
.category-tabs{display:flex;border-bottom:2px solid #dcdcdc;background-color:#efefef align-items: center;gap:.75rem;overflow-x:auto;-ms-overflow-style:none;scrollbar-width:none}.category-tabs::-webkit-scrollbar{display:none}.tab-item{display:inline-block;white-space:nowrap;background-color:transparent;border:none;cursor:pointer;padding:1rem .5rem;font-size:.9rem;font-weight:600;color:#4b5563;border-bottom:3px solid transparent;margin-bottom:-1px;transition:.2s ease-in-out}.tab-item:hover{color:#111827}.tab-item.active{font-weight:700}
</style>
@endpush

@section('content')
{{-- Bagian Tab Kategori --}}
<div class="max-w-5xl mx-auto mt-[210px]"> {{-- Sesuaikan top dengan tinggi header --}}
    <div class=" px-4 ">
        <div class="category-tabs">
            <button class="tab-item active" data-category="all">Semua</button>
            @foreach($categories as $category)
                <button class="tab-item" data-category="{{ $category->slug }}" data-color="{{ $category->color }}">{{ $category->nama_kategori }}</button>
            @endforeach
        </div>
    </div>
</div>

{{-- Konten Utama --}}
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Kolom Utama (Daftar Berita) --}}
        <div class="lg:col-span-2">
            {{-- Kontainer ini akan diisi oleh AJAX --}}
            <div id="news-list-container" class="space-y-4">
                {{-- Menampilkan berita awal saat halaman dimuat --}}
                @include('news.components.news-list-item', ['newsItems' => $news])
            </div>

            {{-- Tombol Load More --}}
            <div id="load-more-wrapper" class="mt-8 text-center">
                <button id="load-more-btn" class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-blue-700 transition-all">
                    <span id="btn-text">Tampilkan Lebih Banyak</span>
                    <span id="btn-loader" class="hidden"><i class="fas fa-spinner fa-spin"></i> Memuat...</span>
                </button>
            </div>
        </div>

        {{-- Sidebar (Berita Populer) --}}
        <aside class="lg:col-span-1 space-y-6">
            @include('news.sidebar', [
                'news' => $news, 
                'relatedNews' => $relatedNews ?? [], 
                'popularNews' => $popularNews ?? []
            ])
            @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])
        </aside>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabsContainer = document.querySelector('.category-tabs');
    const newsContainer = document.getElementById('news-list-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreWrapper = document.getElementById('load-more-wrapper');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    // ... (Fungsi fetchNews() dan event listener loadMoreBtn tidak perlu diubah) ...
    let currentPage = 1;
    let currentCategory = 'all';
    let isLoading = false;

    function fetchNews(isTabChange = false) {
        if (isLoading) return;
        isLoading = true;

        if (isTabChange) {
            currentPage = 1;
        } else {
            currentPage++;
        }

        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
        if (isTabChange) {
            newsContainer.classList.add('loading');
        }

        let apiUrl = `{{ route('api.news.load_more') }}?page=${currentPage}&category=${currentCategory}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (isTabChange) {
                    newsContainer.innerHTML = data.html;
                } else {
                    newsContainer.insertAdjacentHTML('beforeend', data.html);
                }

                if (data.hasMorePages) {
                    loadMoreWrapper.style.display = 'block';
                } else {
                    loadMoreWrapper.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching news:', error))
            .finally(() => {
                isLoading = false;
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
                newsContainer.classList.remove('loading');
            });
    }

    // === PERBAIKAN UTAMA ADA DI DALAM EVENT LISTENER INI ===
    tabsContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('tab-item')) {
            
            // 1. Reset semua tab ke kondisi awal
            tabsContainer.querySelectorAll('.tab-item').forEach(tab => {
                tab.classList.remove('active');
                tab.style.setProperty('color', ''); // Cara reset yang lebih aman
                tab.style.setProperty('border-bottom-color', '');
            });

            // 2. Aktifkan tab yang baru diklik
            const clickedTab = e.target;
            clickedTab.classList.add('active');
            
            // 3. Ambil warna dari atribut data-color
            const activeColor = clickedTab.dataset.color;

            // 4. Terapkan warna jika ada
            if (activeColor) {
                // Gunakan setProperty untuk menambahkan !important
                clickedTab.style.setProperty('color', activeColor, 'important');
                clickedTab.style.setProperty('border-bottom-color', activeColor, 'important');
            } else {
                // Untuk tab "Semua", gunakan warna default merah
                clickedTab.style.setProperty('color', '#be123c', 'important');
                clickedTab.style.setProperty('border-bottom-color', '#be123c', 'important');
            }
            
            // 5. Ambil data berita (tidak berubah)
            currentCategory = clickedTab.dataset.category;
            fetchNews(true);
        }
    });

    loadMoreBtn.addEventListener('click', function() {
        fetchNews(false); 
    });
});
</script>
@endpush