@extends('layouts.app')

@section('title', 'Video Terbaru - Portal Berita')

@section('content')
<div class="bg-gray-50 min-h-screen">
    {{-- Header Component --}}


    {{-- Breadcrumb Component --}}
    @include('videos.components.breadcrumb')

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3 space-y-8">
                {{-- Featured Videos Component --}}
                @include('videos.components.featured-videos', ['featuredVideos' => $featuredVideos])

                {{-- Filter Bar Component --}}
                @include('videos.components.filter-bar', [
                    'categories' => $categories,
                    'sort' => $sort,
                    'perPage' => $perPage,
                    'paginationInfo' => $paginationInfo
                ])

                {{-- Video Grid Component --}}
                @include('videos.components.video-grid', ['videos' => $videos])

                {{-- Pagination Component --}}
                @include('videos.components.pagination', [
                    'videos' => $videos,
                    'paginationInfo' => $paginationInfo
                ])
            </div>

            {{-- Sidebar Component --}}
            @include('videos.components.sidebar', [
                'categories' => $categories,
                'sort' => $sort,
                'paginationInfo' => $paginationInfo
            ])
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when sort or per_page changes
    const sortSelect = document.getElementById('sort');
    const perPageSelect = document.getElementById('per_page');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Professional focus states */
input:focus, select:focus, button:focus, a:focus {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Professional hover effects */
.group:hover img {
    transform: scale(1.05);
}

/* Responsive text sizing */
@media (max-width: 768px) {
    .text-4xl {
        font-size: 2rem;
    }
    
    .text-xl {
        font-size: 1.125rem;
    }
    
    .py-12 {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
}
</style>
@endpush
@endsection