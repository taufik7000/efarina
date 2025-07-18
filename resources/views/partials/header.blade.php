<!-- Navigation -->
<nav class="bg-white shadow-lg sticky top-0 z-50 transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                    <i class="fas fa-newspaper mr-2"></i>
                    Portal Berita
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('home') ? 'text-blue-600 font-medium' : '' }}">
                    <i class="fas fa-home mr-1"></i> Beranda
                </a>
                <a href="{{ route('news.index') }}" class="text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('news.*') ? 'text-blue-600 font-medium' : '' }}">
                    <i class="fas fa-newspaper mr-1"></i> Berita
                </a>
                
                <!-- Search -->
                <form method="GET" action="{{ route('news.index') }}" class="flex items-center">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari berita..." 
                               class="w-64 px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </form>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('home') ? 'text-blue-600 font-medium' : '' }}">
                    <i class="fas fa-home mr-2"></i> Beranda
                </a>
                <a href="{{ route('news.index') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('news.*') ? 'text-blue-600 font-medium' : '' }}">
                    <i class="fas fa-newspaper mr-2"></i> Berita
                </a>
                
                <!-- Mobile Search -->
                <form method="GET" action="{{ route('news.index') }}" class="px-3 py-2">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari berita..." 
                               class="w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </form>
            </div>
        </div>
    </div>
</nav>