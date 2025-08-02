@extends('layouts.app')

@section('title', 'Karir di Efarina TV')

@section('content')
<div class="bg-gray-50">
    {{-- Hero Section --}}
    <section class="bg-blue-900 text-white py-20">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">Bergabunglah dengan Tim Kami</h1>
            <p class="text-lg lg:text-xl text-blue-200 max-w-3xl mx-auto">
                Jadilah bagian dari tim yang dinamis dan inovatif di industri media. Temukan peran Anda di Efarina TV.
            </p>
        </div>
    </section>

    {{-- Daftar Lowongan --}}
    <div class="max-w-5xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-10">Lowongan Tersedia</h2>

        @if($vacancies->count() > 0)
            <div class="space-y-6">
                @foreach($vacancies as $vacancy)
                    <a href="{{ route('career.show', $vacancy->slug) }}" class="block bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-500 transition-all duration-300">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 group-hover:text-blue-700">{{ $vacancy->title }}</h3>
                                <div class="flex items-center text-sm text-gray-500 mt-2 space-x-4">
                                    <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2"></i>{{ $vacancy->location }}</span>
                                    <span class="flex items-center"><i class="fas fa-briefcase mr-2"></i>{{ $vacancy->job_type }}</span>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0 flex-shrink-0">
                                <span class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-semibold rounded-full group-hover:bg-blue-700">
                                    Lihat Detail
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12">
                {{ $vacancies->links('videos.components.pagination') }} {{-- Menggunakan komponen pagination yang sudah ada --}}
            </div>
        @else
            <div class="text-center bg-white p-12 rounded-lg border border-gray-200">
                <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700">Belum Ada Lowongan</h3>
                <p class="text-gray-500 mt-2">Saat ini belum ada lowongan pekerjaan yang tersedia. Silakan periksa kembali di lain waktu.</p>
            </div>
        @endif
    </div>
</div>
@endsection