@extends('layouts.app')

@section('title', 'Live Streaming - Efarina TV')

@push('styles')
<style>
    .live-player-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        background-color: #000;
        border-radius: 0.75rem;
    }
    .live-player-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endpush

@section('content')
<main class="max-w-5xl mx-auto px-4 py-8 mt-[50px] lg:mt-[150px]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Kolom Konten Utama (2/3) --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Area Live Streaming --}}
            <section>


                <div class="live-player-wrapper shadow-2xl">
                    <iframe 
                        src="https://live.efarinatv.net/" 
                        title="Efarina TV Live Streaming" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <p>
                        <i class="fas fa-info-circle mr-2"></i>
                        Jika streaming tidak muncul, coba segarkan halaman atau pastikan koneksi internet Anda stabil.
                    </p>

                    
                </div>

                            {{-- KOTAK IKLAN (Sesuai home.blade.php) --}}
            <div class="bg-gray-200 rounded-lg text-center p-8 mt-6"><a href="/ads">
                <p class="text-sm text-gray-600">Ads</p>
                <h4 class="text-base font-semibold text-gray-700">Pasang Iklan Anda Disini</h4></a>
            </div>
            </section>
            
            <hr class="border-gray-200">

         <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Jangan Lewatkan</h2>
                
                @if($videos->isNotEmpty())
                    {{-- Video Grid Component --}}
                    @include('videos.components.video-grid', ['videos' => $videos])

                    {{-- 
                        Pagination Component (menggunakan struktur yang Anda inginkan)
                        Ini sekarang akan berfungsi karena $paginationInfo sudah ada.
                    --}}
                    @include('videos.components.pagination', [
                        'videos' => $videos,
                        'paginationInfo' => $paginationInfo
                    ])
                @else
                    <p class="text-center text-gray-500">Video terbaru belum tersedia.</p>
                @endif
            </section>

        </div>

        {{-- Sidebar (1/3) --}}
        <aside class="lg:col-span-1 space-y-6">
            {{-- Video Unggulan --}}
            {{-- Menggunakan komponen sidebar yang sudah ada --}}
            @if($featuredVideos->isNotEmpty())
                @include('components.featured-videos-sidebar', ['featuredVideos' => $featuredVideos])
            @else
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Video Unggulan</h3>
                    <p class="text-center text-gray-500">Video unggulan belum tersedia.</p>
                </div>
            @endif
        </aside>

    </div>
</main>
@endsection