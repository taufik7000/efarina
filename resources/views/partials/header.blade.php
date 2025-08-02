<header class="backdrop-blur-fallback shadow-lg fixed top-0 left-0 right-0 z-50 transition-transform duration-300" id="header">
    <!-- Top Bar dengan Logo -->
    <div class="bg-white border-b border-gray-200 hidden lg:block" id="top-bar">
        <div class="max-w-5xl mx-auto py-3 px-1">
            <div class="flex items-center justify-between h-19">
                <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    {{-- Ganti div tulisan dengan tag img --}}
                <img src="{{ asset('storage/assets/logo-efarina.webp') }}" alt="Logo Efarina TV" class="px-4 h-12 w-auto">
                </a>
            </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->

                    <!-- Mobile Menu Toggle -->
                    <button id="mobile-menu-toggle" class="header-button lg:hidden p-2 text-gray-600 transition-colors" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#4b5563'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar - Updated dengan Warna Biru -->
    <div class="bg-blue-950" id="nav-bar">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Main Navigation -->
                <nav class="hidden lg:flex items-center space-x-0">
                    <!-- Home Icon -->
                    <a href="{{ route('home') }}" class="flex items-center justify-center w-12 h-12 text-white hover:bg-blue-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    </a>
                    
                    <!-- Navigation Links -->
                    <a href="/berita" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Terbaru
                    </a>
                    <a href="/berita/kategori/sumut" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Sumut
                    </a>
                    <a href="/berita/kategori/nasional" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Nasional
                    </a>
                    <a href="/berita/kategori/internasional" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Internasional
                    </a>
                    <a href="/berita/kategori/kesehatan" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Kesehatan
                    </a>
                    <a href="#" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        Efarina Daily
                    </a>
                    <a href="/video" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        VIDEO
                    </a>
                    <a href="{{ route('video.live') }}" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-slate-600">
                        <span class="w-3 h-3 bg-red-600 rounded-full mr-2 animate-pulse"></span> Live Streaming
                    </a>
                </nav>
                 <div class="hidden lg:flex items-center border-l border-slate-600">
                    {{-- Ikon Search --}}
                    <button class="flex items-center justify-center w-12 h-12 text-white hover:bg-blue-900 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                    {{-- Ikon Login --}}
                    <a href="/login" class="flex items-center justify-center w-12 h-12 text-white hover:bg-blue-900 transition-colors border-l border-slate-600">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Navigation (Regional) -->
    <div class="bg-gray-100 border-b border-gray-200" id="secondary-nav">
        <div class="max-w-5xl mx-auto px-4">
            <div class="hidden lg:flex items-center h-10 space-x-0">
                <a href="/berita/kategori/simalungun" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Simalungun</a>        
                <a href="/berita/kategori/pematangsiantar" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Pematangsiantar</a>
                <a href="/berita/kategori/medan" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Medan</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Tebing Tinggi</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Kisaran</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Karo</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Asahan</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Batu Bara</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Tanjung Balai</a>

            </div>
        </div>
    </div>

    <!-- Mobile Header - Fixed untuk Mobile -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-60 sm:fixed bg-blue-950 shadow-lg" id="mobile-header">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="{{ asset('storage/assets/logo-efarina.webp') }}" alt="Logo Efarina TV" class="h-8 w-auto">
                    </a>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Live Streaming -->
                    <a href="{{ route('video.live') }}" class="flex items-center space-x-2 text-white text-sm">
                        <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
                        <span class="tex-sm">Live Streaming</span>
                    </a>
                    
                    <!-- Login Icon -->
                    <a href="/login" class="text-white">
                        <i class="fas fa-user-circle text-xl"></i>
                    </a>

                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu" class="lg:hidden hidden fixed inset-0 z-50 bg-black bg-opacity-50" style="top: 60px;">
        <div class="bg-white h-full overflow-y-auto">
            <div class="px-4 py-4 space-y-2">
                <!-- Main Navigation -->
                <div class="space-y-1">
                    <a href="{{ route('home') }}" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Beranda</a>
                    <a href="/berita" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Terbaru</a>
                    <a href="/berita/kategori/sumut" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Sumut</a>
                    <a href="/berita/kategori/nasional" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Nasional</a>
                    <a href="/berita/kategori/internasional" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Internasional</a>
                    <a href="/berita/kategori/kesehatan" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Kesehatan</a>
                    <a href="#" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Efarina Daily</a>
                    <a href="/video" class="block px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors border-b border-gray-100">Video</a>
                </div>
                
                <!-- Regional Navigation -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="px-3 py-2 text-gray-500 font-semibold text-sm uppercase tracking-wider">Regional</h3>
                    <div class="space-y-1">
                        <a href="/berita/kategori/simalungun" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Simalungun</a>
                        <a href="/berita/kategori/pematangsiantar" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Pematangsiantar</a>
                        <a href="/berita/kategori/medan" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Medan</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Tebing Tinggi</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Kisaran</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Karo</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Asahan</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Batu Bara</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Tanjung Balai</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Overlay -->
    <div id="search-overlay" class="hidden absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-40">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="relative">
                <input type="text" placeholder="Cari berita..." class="w-full px-4 py-3 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button id="search-close" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<style>
/* CSS untuk efek scroll */
.hide-top-bar #top-bar {
    transform: translateY(-100%);
    opacity: 0;
}

.hide-top-bar #nav-bar,
.hide-top-bar #secondary-nav {
    transition: transform 0.3s ease;
}

.hide-top-bar {
    transform: translateY(-100px); /* Sesuaikan dengan tinggi top bar */
}

/* Smooth transition untuk semua elemen */
#top-bar {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

#header {
    transition: transform 0.3s ease;
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastScrollTop = 0;
    const header = document.getElementById('header');
    const topBar = document.getElementById('top-bar');
    const topBarHeight = topBar.offsetHeight;
    

    // Scroll event handler
    window.addEventListener('scroll', function() {
         const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > topBarHeight) {
            // Past top bar height - hide top bar
            header.classList.add('hide-top-bar');
            header.style.transform = `translateY(-${topBarHeight}px)`;
        } else {
            // At the very top - show everything
            header.classList.remove('hide-top-bar');
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    }, false);
});
</script>