@extends('layouts.app')

@section('title', 'Karir: ' . $jobVacancy->title)

@section('content')
<div class="bg-white">
    {{-- Header Lowongan --}}
    <div class="py-16 bg-gray-50 border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4">
            <a href="{{ route('career.index') }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block"><i class="fas fa-arrow-left mr-2"></i>Kembali ke semua lowongan</a>
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900">{{ $jobVacancy->title }}</h1>
            <div class="flex items-center text-md text-gray-600 mt-4 space-x-6">
                <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>{{ $jobVacancy->location }}</span>
                <span class="flex items-center"><i class="fas fa-briefcase mr-2 text-gray-400"></i>{{ $jobVacancy->job_type }}</span>
                <span class="flex items-center"><i class="fas fa-calendar-times mr-2 text-gray-400"></i>Batas Akhir: {{ $jobVacancy->application_deadline->translatedFormat('d F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Detail Konten --}}
    <div class="max-w-5xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            {{-- Kolom Kiri: Deskripsi & Persyaratan --}}
            <div class="lg:col-span-2 prose max-w-none">
                <h2 class="text-2xl font-bold mb-4">Deskripsi Pekerjaan</h2>
                {!! $jobVacancy->description !!}

                <h2 class="text-2xl font-bold mt-10 mb-4">Persyaratan</h2>
                {!! $jobVacancy->requirements !!}
            </div>

            {{-- Kolom Kanan: Tombol Lamar --}}
            <aside class="lg:col-span-1">
                <div class="sticky top-28 bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Lamar Posisi Ini</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Kirimkan CV, portofolio, dan dokumen pendukung lainnya sebelum batas akhir yang ditentukan.
                    </p>
                    <a href="#" {{-- Ganti dengan link formulir lamaran --}}
                       class="w-full text-center block bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition-all duration-300">
                        Lamar Sekarang
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection