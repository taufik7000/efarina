<div>
    @if(count($galleryImages) > 0)
    {{-- Gallery Section --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
            </svg>
            Galeri Foto ({{ count($galleryImages) }} foto)
        </h3>
        
        {{-- Gallery Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($galleryImages as $index => $image)
            <div class="gallery-item relative group cursor-pointer overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all duration-300"
                 wire:click="openModal({{ $index }})">
                <img src="{{ $this->getImageUrl($image) }}" 
                     alt="Galeri foto {{ $index + 1 }}" 
                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                
                {{-- Overlay --}}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                        </svg>
                    </div>
                </div>
                
                {{-- Image Number Badge --}}
                <div class="absolute top-2 left-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                    {{ $index + 1 }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Livewire Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-hidden" 
         x-data="{ show: @entangle('showModal') }"
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black bg-opacity-90" 
             wire:click="closeModal">
        </div>
        
        {{-- Modal Content --}}
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <div class="relative max-w-7xl max-h-full flex items-center justify-center">
                
                {{-- Close Button --}}
                <button wire:click="closeModal" 
                        class="absolute -top-12 right-0 z-10 text-white hover:text-gray-300 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="sr-only">Tutup</span>
                </button>
                
                {{-- Navigation Buttons --}}
                @if(count($galleryImages) > 1)
                <button wire:click="previousImage" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-3 rounded-full transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="sr-only">Foto Sebelumnya</span>
                </button>
                
                <button wire:click="nextImage" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-3 rounded-full transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="sr-only">Foto Selanjutnya</span>
                </button>
                @endif
                
                {{-- Main Image --}}
                <div class="relative">
                    <img src="{{ $this->getImageUrl($this->getCurrentImage()) }}" 
                         alt="Galeri foto {{ $selectedImageIndex + 1 }}"
                         class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                </div>
                
                {{-- Image Counter --}}
                <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-70 text-white px-4 py-2 rounded-full text-sm">
                    {{ $selectedImageIndex + 1 }} / {{ count($galleryImages) }}
                </div>
                
                {{-- Thumbnail Strip (for large galleries) --}}
                @if(count($galleryImages) > 3)
                <div class="absolute -bottom-20 left-1/2 transform -translate-x-1/2 flex space-x-2 max-w-full overflow-x-auto pb-2">
                    @foreach($galleryImages as $index => $image)
                    <button wire:click="goToImage({{ $index }})" 
                            class="flex-shrink-0 w-12 h-12 rounded overflow-hidden border-2 transition-all
                                   {{ $index === $selectedImageIndex ? 'border-blue-500 opacity-100' : 'border-transparent opacity-60 hover:opacity-80' }}">
                        <img src="{{ $this->getImageUrl($image) }}" 
                             alt="Thumbnail {{ $index + 1 }}"
                             class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- No Gallery Message --}}
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', function() {
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (@this.showModal) {
            switch(e.key) {
                case 'Escape':
                    @this.closeModal();
                    break;
                case 'ArrowLeft':
                    @this.previousImage();
                    break;
                case 'ArrowRight':
                    @this.nextImage();
                    break;
            }
            e.preventDefault();
        }
    });
    
    // Listen to Livewire events
    Livewire.on('modal-opened', () => {
        document.body.style.overflow = 'hidden';
    });
    
    Livewire.on('modal-closed', () => {
        document.body.style.overflow = 'auto';
    });
    
    Livewire.on('image-changed', (event) => {
        console.log('Image changed to index:', event.index);
    });
});
</script>
@endpush