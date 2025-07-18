<!-- Footer -->
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-4">
                    <i class="fas fa-newspaper mr-2"></i>
                    Portal Berita
                </h3>
                <p class="text-gray-300">
                    Menyajikan berita terkini dan terpercaya untuk masyarakat Indonesia.
                </p>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Navigasi</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="{{ route('news.index') }}" class="text-gray-300 hover:text-white transition-colors">Berita</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Hubungi Kami</h4>
                <div class="space-y-2 text-gray-300">
                    <p><i class="fas fa-envelope mr-2"></i> info@portalberita.com</p>
                    <p><i class="fas fa-phone mr-2"></i> (021) 1234-5678</p>
                    <p><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</p>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
            <p>&copy; {{ date('Y') }} Portal Berita. All rights reserved.</p>
        </div>
    </div>
</footer>