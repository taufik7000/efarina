{{-- File: resources/views/news/sidebar.blade.php --}}

<aside class="sidebar">
    <style>
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
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

                {{-- Berita Populer --}}
            @if($popularNews->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-fire text-orange-500 mr-2"></i>
                    Berita Populer
                </h3>
                <div class="space-y-3">
                    @foreach($popularNews->take(8) as $popular)
                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                        <img src="{{ $popular->thumbnail ? asset('storage/' . $popular->thumbnail) : 'https://via.placeholder.com/80x60' }}" 
                             alt="{{ $popular->judul }}" 
                             class="w-16 h-12 object-cover rounded flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 text-sm leading-tight mb-1 hover:text-red-600 transition-colors line-clamp-2">
                                <a href="{{ route('news.show', $popular->slug) }}">{{ Str::limit($popular->judul, 80) }}</a>
                            </h4>
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-eye mr-1"></i>
                                {{ number_format($popular->views_count) }}
                                <span class="mx-2">•</span>
                                <span>{{ $popular->published_at ? $popular->published_at->format('d M') : $popular->created_at->format('d M') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
</aside>