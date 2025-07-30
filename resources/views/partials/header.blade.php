<header class="backdrop-blur-fallback shadow-lg fixed top-0 left-0 right-0 z-50">
    <!-- Top Bar dengan Logo -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto py-3 px-1">
            <div class="flex items-center justify-between h-19">
                <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    {{-- Ganti div tulisan dengan tag img --}}
                <img src="{{ asset('assets/logo-efarina.webp') }}" alt="Logo Efarina TV" class="px-4 h-12 w-auto">
                </a>
            </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->
                    <button class="header-button p-2 text-gray-600 transition-colors" id="search-toggle" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#4b5563'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Info Button -->
                    <button class="header-button p-2 text-gray-600 transition-colors" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#4b5563'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>

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
    <div class="bg-blue-950 header-nav-blue">
        <div class="max-w-6xl mx-auto px-4">
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
                    <a href="/berita" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Terbaru
                    </a>
                    <a href="/berita/kategori/sumut" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Sumut
                    </a>
                    <a href="/berita/kategori/nasional" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Nasional
                    </a>
                    <a href="/berita/kategori/internasional" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Internasional
                    </a>
                    <a href="/berita/kategori/kesehatan" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Kesehatan
                    </a>
                    <a href="#" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        Efarina Daily
                    </a>
                    <a href="/video" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        VIDEO
                    </a>
                    <a href="#" class="px-4 h-12 flex items-center text-white text-sm font-medium hover:bg-blue-900 transition-colors border-l border-blue-800">
                        INDEX
                    </a>
                </nav>

                <!-- Live Streaming Indicator - Updated dengan Warna Biru -->
                <div class="hidden lg:flex items-center space-x-2 bg-blue-600 px-3 py-1 rounded-full header-live-badge">
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    <span  class="text-white text-xs font-medium"><a href="/live">LIVESTREAM</a></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Navigation (Regional) -->
    <div class="bg-gray-100 border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
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
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Tapanuli Utara</a>
                <a href="#" class="px-3 text-sm text-gray-700 hover:text-blue-600 transition-colors border-l border-gray-300">Tapanuli Selatan</a>
            </div>
        </div>
    </div>

    <!-- Mobile Menu - Updated dengan Warna Biru -->
    <div id="mobile-menu" class="lg:hidden hidden bg-white border-b border-gray-200">
        <div class="px-4 py-4 space-y-2">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Beranda</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Terbaru</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Nasional</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Internasional</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Luar Negeri</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Olahraga</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Gaya Hidup</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Efarina Daily</a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">Video</a>
            </div>
            
            <!-- Live Streaming Indicator -->
            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-blue-600 rounded-lg">
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    <span class="text-white text-sm font-medium"><a href="/live">LIVESTREAM</a></span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            // Toggle hamburger icon
            const icon = mobileMenuToggle.querySelector('svg');
            if (mobileMenu.classList.contains('hidden')) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
            }
        });
    }

    // Search toggle
    const searchToggle = document.getElementById('search-toggle');
    const searchOverlay = document.getElementById('search-overlay');
    const searchClose = document.getElementById('search-close');
    const searchInput = searchOverlay?.querySelector('input');
    
    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.toggle('hidden');
            if (!searchOverlay.classList.contains('hidden') && searchInput) {
                searchInput.focus();
            }
        });
    }
    
    if (searchClose && searchOverlay) {
        searchClose.addEventListener('click', function() {
            searchOverlay.classList.add('hidden');
        });
    }
    
    // Close search on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchOverlay && !searchOverlay.classList.contains('hidden')) {
            searchOverlay.classList.add('hidden');
        }
    });

    // Header scroll effect
 
});
</script>