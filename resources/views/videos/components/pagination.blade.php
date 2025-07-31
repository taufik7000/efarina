{{-- Video Pagination Component --}}
@if($videos->hasPages())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div class="text-sm text-gray-700">
            @if($paginationInfo['total'] > 0)
            Menampilkan <span class="font-semibold text-blue-600">{{ $paginationInfo['from'] }}</span> 
            sampai <span class="font-semibold text-blue-600">{{ $paginationInfo['to'] }}</span> 
            dari <span class="font-semibold text-blue-600">{{ $paginationInfo['total'] }}</span> video
            @endif
        </div>

        <div class="flex items-center justify-center space-x-1">
            @if($videos->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
            </span>
            @else
            <a href="{{ $videos->previousPageUrl() }}" 
               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all">
                <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
            </a>
            @endif

            <div class="flex items-center space-x-1">
                @foreach($videos->getUrlRange(max(1, $videos->currentPage() - 2), min($videos->lastPage(), $videos->currentPage() + 2)) as $page => $url)
                    @if($page == $videos->currentPage())
                    <span class="px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg">
                        {{ $page }}
                    </span>
                    @else
                    <a href="{{ $url }}" 
                       class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all">
                        {{ $page }}
                    </a>
                    @endif
                @endforeach
            </div>

            @if($videos->hasMorePages())
            <a href="{{ $videos->nextPageUrl() }}" 
               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all">
                Next<i class="fas fa-chevron-right ml-1"></i>
            </a>
            @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                Next<i class="fas fa-chevron-right ml-1"></i>
            </span>
            @endif
        </div>
    </div>
</div>
@endif