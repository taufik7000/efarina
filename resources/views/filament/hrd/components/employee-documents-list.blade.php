{{-- resources/views/filament/hrd/components/employee-documents-list.blade.php --}}

<div class="space-y-3">
    @forelse($record->employeeDocuments as $document)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 transition-shadow hover:shadow-lg">
            <div class="flex justify-between items-center w-full">
                {{-- Bagian Kiri: Info Dokumen --}}
                <div class="flex items-center space-x-4">
                    @php
                        $types = [
                            'ktp' => ['icon' => '🆔', 'label' => 'KTP', 'color' => 'blue'],
                            'cv' => ['icon' => '📄', 'label' => 'CV/Resume', 'color' => 'green'],
                            'ijazah' => ['icon' => '🎓', 'label' => 'Ijazah', 'color' => 'purple'],
                            'sertifikat' => ['icon' => '📜', 'label' => 'Sertifikat', 'color' => 'orange'],
                            'foto' => ['icon' => '📸', 'label' => 'Foto Profil', 'color' => 'pink'],
                            'npwp' => ['icon' => '🏦', 'label' => 'NPWP', 'color' => 'indigo'],
                            'bpjs' => ['icon' => '🏥', 'label' => 'BPJS', 'color' => 'red'],
                            'kontrak' => ['icon' => '📋', 'label' => 'Kontrak Kerja', 'color' => 'gray'],
                            'other' => ['icon' => '📎', 'label' => 'Lainnya', 'color' => 'gray'],
                        ];
                        $type = $types[$document->document_type] ?? ['icon' => '📄', 'label' => ucfirst($document->document_type), 'color' => 'gray'];
                    @endphp
                    <span class="text-3xl flex-shrink-0">{{ $type['icon'] }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate flex items-center gap-x-2">
                            <span title="{{ $document->file_name }}">{{ Str::limit($document->file_name, 40) }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $type['color'] }}-100 text-{{ $type['color'] }}-800 dark:bg-{{ $type['color'] }}-900 dark:text-{{ $type['color'] }}-200">
                                {{ $type['label'] }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 space-x-2">
                            <span>
                                @if($document->file_size)
                                    @if($document->file_size >= 1048576) {{ number_format($document->file_size / 1048576, 2) }} MB
                                    @elseif($document->file_size >= 1024) {{ number_format($document->file_size / 1024, 2) }} KB
                                    @else {{ $document->file_size }} bytes @endif
                                @else Unknown size @endif
                            </span>
                            <span>•</span>
                            <span>Di-upload {{ $document->uploaded_at ? $document->uploaded_at->diffForHumans() : 'Unknown' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Bagian Kanan: Status & Aksi --}}
                <div class="flex items-center space-x-4">
                    {{-- Status Verifikasi --}}
                    <div>
                        @if($document->is_verified)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                Terverifikasi
                            </span>
                            @if($document->verifier)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-right">oleh {{ $document->verifier->name }}</p>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                Pending
                            </span>
                        @endif
                    </div>

                    {{-- Tombol Aksi Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-20"
                             style="display: none;">
                            <div class="py-1">
                                @if(Storage::disk('public')->exists($document->file_path))
                                    @php
                                        $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                        $canPreview = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                    @endphp
                                    @if($canPreview)
                                        <a href="{{ Storage::disk('public')->url($document->file_path) }}" target="_blank" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Preview
                                        </a>
                                    @endif
                                    <a href="{{ Storage::disk('public')->url($document->file_path) }}" download="{{ $document->file_name }}" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download
                                    </a>
                                @endif

                                @if(!$document->is_verified)
                                    <button onclick="verifyDocument({{ $document->id }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Verifikasi
                                    </button>
                                @else
                                    <button onclick="unverifyDocument({{ $document->id }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Batal Verifikasi
                                    </button>
                                @endif
                                
                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                <button onclick="deleteDocument({{ $document->id }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/50 dark:text-red-400">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        {{-- Empty State (Tidak ada perubahan, sudah bagus) --}}
        <div class="text-center py-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
            <div class="mx-auto h-20 w-20 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">Belum Ada Dokumen</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Data dokumen untuk {{ $record->name }} masih kosong.
            </p>
            <div class="mt-6">
                <button onclick="openUploadModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Upload Dokumen Pertama
                </button>
            </div>
        </div>
    @endforelse
</div>

{{-- JavaScript (Tidak ada perubahan, masih fungsional) --}}
<script>
    function verifyDocument(documentId) {
        if (confirm('Apakah Anda yakin ingin memverifikasi dokumen ini?')) {
            fetch(`/hrd/employee-documents/${documentId}/verify`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(res => res.json()).then(data => data.success ? location.reload() : alert('Gagal: ' + data.message))
                .catch(err => alert('Terjadi kesalahan.'));
        }
    }

    function unverifyDocument(documentId) {
        if (confirm('Apakah Anda yakin ingin membatalkan verifikasi dokumen ini?')) {
            fetch(`/hrd/employee-documents/${documentId}/unverify`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(res => res.json()).then(data => data.success ? location.reload() : alert('Gagal: ' + data.message))
                .catch(err => alert('Terjadi kesalahan.'));
        }
    }

    function deleteDocument(documentId) {
        if (confirm('PERHATIAN: Tindakan ini akan menghapus file secara permanen dan tidak dapat dibatalkan. Lanjutkan?')) {
            fetch(`/hrd/employee-documents/${documentId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(res => res.json()).then(data => data.success ? location.reload() : alert('Gagal: ' + data.message))
                .catch(err => alert('Terjadi kesalahan.'));
        }
    }

    function openUploadModal() {
        // Asumsi Anda punya tombol trigger di tempat lain dengan atribut ini
        document.querySelector('[data-modal-id="upload_document_modal-action"]')?.click();
    }
</script>