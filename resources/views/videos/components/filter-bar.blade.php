{{-- Video Filter Bar Component --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <div class="w-2 h-6 bg-gradient-to-b from-slate-600 to-slate-800 rounded-full mr-3"></div>
            Filter & Pencarian
        </h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('video.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search Input --}}
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Video</label>
                    <div class="relative">
                        <input type="text" 
                               id="search"
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Masukkan kata kunci..." 
                               class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category" id="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->nama_kategori }} ({{ $cat->videos_count }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Urutkan</label>
                    <select name="sort" id="sort" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>Terpopuler</option>
                        <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        <option value="title" {{ $sort == 'title' ? 'selected' : '' }}>Judul A-Z</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        <i class="fas fa-search mr-2"></i>Cari Video
                    </button>
                    @if(request('search') || request('category') || request('sort') != 'latest')
                    <a href="{{ route('video.index') }}" class="bg-gray-500 text-white px-4 py-3 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                    @endif
                </div>
                
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    @if($paginationInfo['total'] > 0)
                    <span class="bg-gray-100 px-3 py-2 rounded-lg font-medium">
                        {{ $paginationInfo['from'] }}-{{ $paginationInfo['to'] }} dari {{ $paginationInfo['total'] }} video
                    </span>
                    @endif
                    
                    <div class="flex items-center space-x-2">
                        <label for="per_page" class="font-medium">Tampilkan:</label>
                        <select name="per_page" id="per_page" class="border border-gray-300 rounded-lg px-3 py-2" onchange="this.form.submit()">
                            <option value="6" {{ $perPage == 6 ? 'selected' : '' }}>6</option>
                            <option value="12" {{ $perPage == 12 ? 'selected' : '' }}>12</option>
                            <option value="24" {{ $perPage == 24 ? 'selected' : '' }}>24</option>
                            <option value="48" {{ $perPage == 48 ? 'selected' : '' }}>48</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>