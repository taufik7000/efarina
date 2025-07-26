@php
    // Ambil data lampiran dari record utama
    $attachments = $getRecord()->attachments ?? [];
    $attachments = is_array($attachments) ? $attachments : [];
@endphp

{{-- Gunakan Alpine.js untuk mengelola state modal --}}
<div x-data="{ 
        isModalOpen: false, 
        modalImageUrl: '',
        openModal(imageUrl) {
            this.modalImageUrl = imageUrl;
            this.isModalOpen = true;
        } 
    }" 
     @keydown.escape.window="isModalOpen = false">

    {{-- Tampilkan daftar lampiran jika ada --}}
    @if (count($attachments) > 0)
        <ul role="list" class="divide-y divide-gray-200 dark:divide-white/10 border-t border-b dark:border-white/10">
            @foreach ($attachments as $attachment)
                @php
                    $filePath = $attachment['filename'] ?? null;
                    // Cek apakah file ini adalah gambar
                    $isImage = $filePath && in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                @endphp
                <li class="py-4">
                    <div class="flex items-start gap-x-4">
                        {{-- Tampilkan Thumbnail jika ini adalah gambar --}}
                        @if($isImage)
                        <div class="flex-shrink-0">
                            <img @click="openModal('{{ Storage::url($filePath) }}')" 
                                 src="{{ Storage::url($filePath) }}" 
                                 alt="Preview" 
                                 class="h-16 w-16 rounded-lg object-cover cursor-pointer hover:opacity-80 transition-opacity">
                        </div>
                        @endif

                        {{-- Info file --}}
                        <div class="flex-1">
                            <p class="font-semibold text-primary-600 dark:text-primary-400">
                                {{-- Link untuk membuka modal (jika gambar) atau file di tab baru --}}
                                <a  @if($isImage) 
                                        @click.prevent="openModal('{{ Storage::url($filePath) }}')" href="#" 
                                    @else 
                                        href="{{ Storage::url($filePath) }}" target="_blank" 
                                    @endif
                                    class="inline-flex items-center gap-1">
                                    <x-heroicon-o-paper-clip class="h-5 w-5"/>
                                    <span>{{ basename($filePath) }}</span>
                                </a>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $attachment['description'] ?? 'Tidak ada deskripsi.' }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                Diupload oleh: {{ $attachment['uploaded_by'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        {{-- Pesan jika tidak ada lampiran --}}
        <p class="text-center text-gray-500 dark:text-gray-400 py-4">📎 Tidak ada lampiran.</p>
    @endif

    <div x-show="isModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
         x-cloak>
        <div @click.outside="isModalOpen = false" class="relative max-w-4xl w-full max-h-[90vh] p-4">
            <img :src="modalImageUrl" alt="Tampilan Penuh" class="object-contain w-full h-full max-h-[85vh] rounded-lg">
            <button @click="isModalOpen = false" class="absolute top-0 right-0 -m-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-700 transition">
                <x-heroicon-o-x-mark class="w-6 h-6"/>
            </button>
        </div>
    </div>
</div>