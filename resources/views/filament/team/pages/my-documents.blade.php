<x-filament-panels::page>
    {{-- Tombol "Upload Dokumen Baru" akan otomatis muncul di header halaman ini. --}}
    {{-- Tidak perlu lagi memanggilnya secara manual di sini. --}}

    {{-- Daftar Dokumen --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Dokumen Saya</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Berikut adalah daftar dokumen yang telah Anda upload. Dokumen akan diverifikasi oleh tim HRD.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Jenis Dokumen</th>
                        <th scope="col" class="px-6 py-3">Nama File</th>
                        <th scope="col" class="px-6 py-3">Tanggal Upload</th>
                        <th scope="col" class="px-6 py-3 text-center">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $doc->document_type_name }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ $doc->file_url }}" target="_blank" class="text-primary-600 hover:underline">
                                    {{ Str::limit($doc->file_name, 30) }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                {{ $doc->uploaded_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($doc->is_verified)
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
                            </td>
                            <td class="px-6 py-4 text-right">
                                {{-- Pemanggilan aksi hapus tetap sama --}}
                                
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-center">
                                    <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada dokumen</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload dokumen pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>