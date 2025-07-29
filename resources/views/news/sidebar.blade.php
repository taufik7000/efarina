{{-- File: resources/views/news/sidebar.blade.php --}}

<aside class="sidebar">
    <style>
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .sidebar-card {
        background: var(--background);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        background: var(--background-light);
    }

    .sidebar-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
        color: var(--text-primary);
    }

    .sidebar-content {
        padding: 1.1rem;
    }

    /* Popular News */
    .popular-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }

    .popular-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .popular-item:first-child {
        padding-top: 0;
    }

    .popular-item:hover {
        background: var(--background-light);
        margin: 0 -1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        border-radius: 0.5rem;
    }

    .popular-rank {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        font-size: 0.875rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .popular-rank.rank-1 { background: #ef4444; }
    .popular-rank.rank-2 { background: #f97316; }
    .popular-rank.rank-3 { background: #eab308; }

    .popular-content {
        flex: 1;
        min-width: 0;
    }

    .popular-title {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .popular-meta {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Categories */
    .category-item {
        display: flex;
        align-items: center;
        justify-content: between;
        padding: 0.75rem 1rem;
        margin: 0 -1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }

    .category-item:hover {
        background: var(--background-light);
    }

    .category-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .category-color {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .category-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .category-count {
        background: var(--background-light);
        color: var(--text-secondary);
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Recent News */
    .recent-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }

    .recent-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .recent-item:first-child {
        padding-top: 0;
    }

    .recent-item:hover {
        background: var(--background-light);
        margin: 0 -1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        border-radius: 0.5rem;
    }

    .recent-image {
        width: 4rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 0.375rem;
        flex-shrink: 0;
    }

    .recent-content {
        flex: 1;
        min-width: 0;
    }

    .recent-title {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .recent-meta {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    /* Related News */
    .related-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }

    .related-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .related-item:first-child {
        padding-top: 0;
    }

    .related-item:hover {
        background: var(--background-light);
        margin: 0 -1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        border-radius: 0.5rem;
    }

    .related-image {
        width: 4rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 0.375rem;
        flex-shrink: 0;
    }

    .related-content {
        flex: 1;
        min-width: 0;
    }

    .related-title {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .related-meta {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    /* Newsletter */
    .newsletter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .newsletter-input {
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .newsletter-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .newsletter-btn {
        background: var(--primary);
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .newsletter-btn:hover {
        background: var(--primary-hover);
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .sidebar {
            grid-column: 1;
            grid-row: 2;
        }
    }

    @media (max-width: 768px) {
        .sidebar-content {
            padding: 1rem;
        }
        
        .sidebar-header {
            padding: 1rem;
        }
    }
    </style>

    {{-- Popular News --}}
    @if(isset($popularNews) && $popularNews->count() > 0)
    <div class="sidebar-card">
        <div class="sidebar-header">
        <h3 class="sidebar-title flex items-center">
        {{-- Menggunakan ikon "outline" dari Heroicons --}}
        <x-heroicon-o-sparkles class="w-6 h-6 mr-2 text-red-600"/>
        Trending
    </h3>
        </div>
        <div class="sidebar-content">
            @foreach($popularNews as $index => $popular)
            <a href="{{ route('news.show', $popular->slug) }}" class="popular-item">
                <div class="popular-rank rank-{{ $index + 1 <= 3 ? $index + 1 : 'default' }}">
                    {{ $index + 1 }}
                </div>
                <div class="popular-content">
                    <h4 class="popular-title">{{ Str::limit($popular->judul, 60) }}</h4>
                    <div class="popular-meta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                        </svg>
                        <span>{{ number_format($popular->views_count ?? 0) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Related News --}}
    @if(isset($relatedNews) && $relatedNews->count() > 0)
    <div class="sidebar-card">
        <div class="sidebar-header">
            <h3 class="sidebar-title flex items-center">
        {{-- Menggunakan ikon "outline" dari Heroicons --}}
        <x-heroicon-o-sparkles class="w-6 h-6 mr-2 text-eslate-600"/>
                 Berita Terkait</h3>
        </div>
        <div class="sidebar-content">
            @foreach($relatedNews as $related)
            <a href="{{ route('news.show', $related->slug) }}" class="related-item">
                <img src="{{ $related->thumbnail ? asset('storage/' . $related->thumbnail) : 'https://via.placeholder.com/64x48' }}" 
                     alt="{{ $related->judul }}" 
                     class="related-image">
                <div class="related-content">
                    <h4 class="related-title">{{ Str::limit($related->judul, 70) }}</h4>
                    <div class="related-meta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                        {{ $related->created_at->format('d M Y') }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent News --}}
    <div class="sidebar-card">
        <div class="sidebar-header">
            <h3 class="sidebar-title">🕒 Terbaru</h3>
        </div>
        <div class="sidebar-content">
            @php
            $recentNews = \App\Models\News::with(['category'])
                ->where('status', 'published')
                ->where('id', '!=', $news->id)
                ->latest()
                ->limit(5)
                ->get();
            @endphp
            
            @foreach($recentNews as $recent)
            <a href="{{ route('news.show', $recent->slug) }}" class="recent-item">
                <img src="{{ $recent->thumbnail ? asset('storage/' . $recent->thumbnail) : 'https://via.placeholder.com/64x48' }}" 
                     alt="{{ $recent->judul }}" 
                     class="recent-image">
                <div class="recent-content">
                    <h4 class="recent-title">{{ Str::limit($recent->judul, 60) }}</h4>
                    <div class="recent-meta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                        {{ $recent->created_at->format('d M Y') }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</aside>