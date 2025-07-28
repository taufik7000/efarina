<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border p-4 border-gray-200 dark:border-gray-700">
        
        @if ($contractDocument)
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detail Kontrak Kerja Anda</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Dokumen ini diunggah dan diverifikasi oleh tim HRD.
                </p>
            </div>
            
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Dokumen</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 col-span-2">{{ $contractDocument->document_type_name }}</dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 col-span-2">{{ $contractDocument->file_name }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Upload</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 col-span-2">{{ $contractDocument->uploaded_at->format('d F Y') }}</dd>
                    </div>
                     <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 col-span-2">
                            @if($contractDocument->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <x-heroicon-s-clock class="w-4 h-4 mr-1"/>
                                    Menunggu Verifikasi
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lampiran</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 col-span-2">
                             <x-filament::button
                                tag="a"
                                href="{{ $contractDocument->file_url }}"
                                target="_blank"
                                icon="heroicon-o-arrow-down-tray"
                                color="primary"
                            >
                                Unduh Kontrak
                            </x-filament::button>
                        </dd>
                    </div>
                </dl>
            </div>

        @else
            {{-- Tampilan jika dokumen tidak ditemukan --}}
            <div class="p-12 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12" />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Kontrak Kerja Tidak Ditemukan</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kontrak kerja Anda belum diunggah oleh tim HRD.<br>
                    Silahkan Hubungi tim HRD untuk informasi lebih lanjut.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>