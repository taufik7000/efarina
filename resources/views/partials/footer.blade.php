<footer class="bg-indigo-950">
    <div class="container mx-auto lg:pt-24 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Kolom 1: Logo dan Deskripsi --}}
            <div class="space-y-4">
                <a href="{{ route('home') }}" class="inline-block">
                    {{-- Pastikan path logo sudah benar --}}
                    <img src="{{ asset('storage/assets/logo-efarina.webp') }}" alt="Logo Efarina TV" class="h-20">
                </a>
                <p class="text-sm text-gray-400">
                    Efarina TV dapat dinikmati di seluruh Indonesia dengan memilih Satelit Telkom 4 H 3978.
                </p>
                {{-- Ikon Media Sosial --}}
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                </div>
            </div>

            {{-- Kolom 2: Informasi --}}
            <div>
                <h3 class="text-white font-semibold text-lg mb-4">Informasi</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Tentang</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kontak</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Pedoman Media</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Karir</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Program --}}
            <div>
                <h3 class="text-white font-semibold text-lg mb-4">Program</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Si Ucok</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Dendang Irama</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Sumut Terkini</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Marragam Ragam</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Etah Melalak</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Berita --}}
            <div>
                <h3 class="text-white font-semibold text-lg mb-4">Berita</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Simalungun</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Pematangsiantar</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Batu Bara</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Asahan</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Tanjung Balai</a></li>
                </ul>
            </div>

        </div>

        {{-- Bagian Bawah Footer: Hak Cipta --}}
        <div class="border-t border-gray-700 pt-6 mt-8">
            <p class="text-center text-sm text-gray-500">
                Efarina TV © {{ date('Y') }} All rights Reserved.
            </p>
        </div>

    </div>
</footer>