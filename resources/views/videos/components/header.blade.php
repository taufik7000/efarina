{{-- Video Header Component - Desain Diperbarui --}}
<section class="bg-gradient-to-b from-gray-900 via-blue-950 to-black text-white relative overflow-hidden">
    
    <!-- Efek Cahaya Latar Belakang -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.3),rgba(255,255,255,0))] opacity-70"></div>
    
    <!-- Pola Latar Belakang Halus -->
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="white"><path d="M0 .5H31.5V32"/></svg>'); background-size: 32px 32px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="text-center">
            
            <!-- Ikon dengan Efek Glassmorphism -->
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl mb-8 shadow-xl">
                <i class="fas fa-play text-3xl text-white"></i>
            </div>
            
            <!-- Judul Utama dengan Gradien Teks -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-4 bg-gradient-to-b from-white to-slate-300 bg-clip-text text-transparent">
                Video Terbaru
            </h1>
            
            <p class="text-lg md:text-xl text-slate-300 mb-12 max-w-3xl mx-auto">
                Tonton koleksi video terbaru dan terpopuler dari channel resmi kami, disajikan dalam kualitas terbaik.
            </p>
            
            <!-- Statistik dengan Kartu Kaca -->
            <div class="max-w-4xl mx-auto bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <dl class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-10">
                    <div class="flex flex-col items-center">
                        <dd class="text-3xl lg:text-4xl font-semibold bg-gradient-to-b from-blue-300 to-cyan-300 bg-clip-text text-transparent">{{ $totalVideos }}</dd>
                        <dt class="mt-1 text-sm text-slate-400 uppercase tracking-wider">Total Video</dt>
                    </div>
                    <div class="flex flex-col items-center">
                        <dd class="text-3xl lg:text-4xl font-semibold bg-gradient-to-b from-blue-300 to-cyan-300 bg-clip-text text-transparent">{{ $totalCategories }}</dd>
                        <dt class="mt-1 text-sm text-slate-400 uppercase tracking-wider">Kategori</dt>
                    </div>
                    <div class="flex flex-col items-center">
                        <dd class="text-3xl lg:text-4xl font-semibold bg-gradient-to-b from-blue-300 to-cyan-300 bg-clip-text text-transparent">{{ $featuredCount }}</dd>
                        <dt class="mt-1 text-sm text-slate-400 uppercase tracking-wider">Unggulan</dt>
                    </div>
                    <div class="flex flex-col items-center">
                        <dd class="text-3xl lg:text-4xl font-semibold bg-gradient-to-b from-blue-300 to-cyan-300 bg-clip-text text-transparent">HD+</dd>
                        <dt class="mt-1 text-sm text-slate-400 uppercase tracking-wider">Kualitas</dt>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>