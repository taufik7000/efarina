@extends('layouts.app')

@section('title', 'Tentang Kami - Efarina TV')

@push('styles')
<style>
    /* Style untuk memberikan efek overlay pada gambar hero */
    .hero-bg-image {
        background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://yt3.googleusercontent.com/p2zT-gIXGwZC2El3Hf5vsiQtR2ujAGo1hn7OiUNiFLaamq8Ed8oXsUD4PeQG_pdLnugqZhWHoTA=w1707-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj');
        background-size: cover;
        background-position: center;
        min-height:350px;
    }
    /* Efek halus saat card di-hover */
    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="hero-bg-image text-white py-24 mt-[120px]">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4 leading-tight animate-fade-in-down text-shadow-30/lg">
            Mengenal Efarina TV Lebih Dekat
        </h1>
        <p class="text-lg lg:text-xl text-gray-200 max-w-3xl mx-auto animate-fade-in-up text-shadow-30/lg">
            Menyajikan informasi yang akurat, mendidik, dan menghibur bagi masyarakat Sumatera Utara dan sekitarnya.
        </p>
    </div>
</section>

<div class="bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-16">

        {{-- Sejarah Kami --}}
        <section class="mb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Perjalanan Kami</h2>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Efarina TV didirikan pada [Tahun Berdiri] dengan semangat untuk menjadi suara lokal yang kuat di tengah arus informasi global. Berawal dari sebuah studio sederhana di [Kota Awal], kami telah tumbuh menjadi salah satu stasiun televisi terkemuka di regional, berkat dedikasi tim dan kepercayaan pemirsa setia kami.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Setiap langkah kami didasari oleh komitmen untuk mengangkat budaya lokal, mendukung perkembangan daerah, dan menyediakan platform bagi talenta-talenta lokal untuk bersinar.
                    </p>
                </div>
                <div class="order-1 md:order-2">
                    <img src="https://images.unsplash.com/photo-1586923395912-257121b0a540?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80"
                         alt="Tim Efarina TV" class="rounded-lg shadow-xl w-full h-full object-cover">
                </div>
            </div>
        </section>

        {{-- Visi & Misi --}}
        <section class="bg-white p-12 rounded-lg shadow-md mb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-eye text-blue-600 mr-3"></i> Visi Kami
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Menjadi sumber informasi dan hiburan terpercaya yang menginspirasi kemajuan dan melestarikan kearifan lokal di Sumatera Utara.
                    </p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-rocket text-blue-600 mr-3"></i> Misi Kami
                    </h3>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Menyajikan program berita yang independen, akurat, dan berimbang.</li>
                        <li>Memproduksi konten kreatif yang mendidik dan menghibur.</li>
                        <li>Menjadi wadah bagi ekspresi budaya dan seni lokal.</li>
                        <li>Berperan aktif dalam pembangunan sosial dan ekonomi masyarakat.</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Nilai-Nilai Kami --}}
        <section class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Nilai-Nilai Kami</h2>
            <p class="text-gray-600 max-w-2xl mx-auto mb-10">Prinsip yang menjadi pedoman kami dalam setiap langkah.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="value-card bg-white p-6 rounded-lg shadow-sm transition-all duration-300">
                    <div class="text-blue-600 text-4xl mb-4"><i class="fas fa-check-circle"></i></div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Integritas</h4>
                    <p class="text-gray-500 text-sm">Menjunjung tinggi kejujuran dan etika dalam setiap pemberitaan.</p>
                </div>
                <div class="value-card bg-white p-6 rounded-lg shadow-sm transition-all duration-300">
                    <div class="text-blue-600 text-4xl mb-4"><i class="fas fa-users"></i></div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Komunitas</h4>
                    <p class="text-gray-500 text-sm">Mengabdi dan menjadi bagian tak terpisahkan dari masyarakat.</p>
                </div>
                <div class="value-card bg-white p-6 rounded-lg shadow-sm transition-all duration-300">
                    <div class="text-blue-600 text-4xl mb-4"><i class="fas fa-lightbulb"></i></div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Kreativitas</h4>
                    <p class="text-gray-500 text-sm">Selalu berinovasi untuk menyajikan konten yang segar dan relevan.</p>
                </div>
                <div class="value-card bg-white p-6 rounded-lg shadow-sm transition-all duration-300">
                    <div class="text-blue-600 text-4xl mb-4"><i class="fas fa-balance-scale"></i></div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Objektivitas</h4>
                    <p class="text-gray-500 text-sm">Menyajikan informasi dari berbagai sudut pandang tanpa memihak.</p>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection