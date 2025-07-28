{{-- resources/views/filament/hrd/components/employee-documents-list.blade.php --}}

<div class="space-y-4">
    @if($record->employeeDocuments->count() > 0)
        @foreach($record->employeeDocuments as $document)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-center">
                    {{-- Document Type --}}
                    <div class="flex items-center space-x-2">
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
                        <span class="text-2xl">{{ $type['icon'] }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $type['color'] }}-100 text-{{ $type['color'] }}-800 dark:bg-{{ $type['color'] }}-900 dark:text-{{ $type['color'] }}-200">
                            {{ $type['label'] }}
                        </span>
                    </div>

                    {{-- File Name --}}
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $document->file_name }}">
                            {{ $document->file_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if($document->file_size)
                                @if($document->file_size >= 1048576)
                                    {{ number_format($document->file_size / 1048576, 2) }} MB
                                @elseif($document->file_size >= 1024)
                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                @else
                                    {{ $document->file_size }} bytes
                                @endif
                            @else
                                Unknown size
                            @endif
                        </p>
                    </div>

                    {{-- Upload Date --}}
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p>{{ $document->uploaded_at ? $document->uploaded_at->diffForHumans() : 'Unknown' }}</p>
                        <p class="text-xs">{{ $document->uploaded_at ? $document->uploaded_at->format('d M Y') : '' }}</p>
                    </div>

                    {{-- Verification Status --}}
                    <div>
                        @if($document->is_verified)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Terverifikasi
                            </span>
                            @if($document->verifier)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">oleh {{ $document->verifier->name }}</p>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Menunggu Verifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="text-sm">
                        @if($document->description)
                            <p class="text-gray-600 dark:text-gray-400 italic">{{ Str::limit($document->description, 50) }}</p>
                        @else
                            <p class="text-gray-400 dark:text-gray-500">Tidak ada keterangan</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center space-x-2">
                        {{-- Preview Button --}}
                        @if(Storage::disk('public')->exists($document->file_path))
                            @php
                                $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                $canPreview = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
                            @endphp
                            
                            @if($canPreview)
                                <a href="{{ Storage::disk('public')->url($document->file_path) }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Preview
                                </a>
                            @endif

                            {{-- Download Button --}}
                            <a href="{{ Storage::disk('public')->url($document->file_path) }}" 
                               download="{{ $document->file_name }}"
                               class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 bg-green-100 rounded hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                        @endif

                        {{-- Verify/Unverify Button --}}
                        @if(!$document->is_verified)
                            <button onclick="verifyDocument({{ $document->id }})"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-600 bg-orange-100 rounded hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:hover:bg-orange-800 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Verifikasi
                            </button>
                        @else
                            <button onclick="unverifyDocument({{ $document->id }})"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Batal
                            </button>
                        @endif

                        {{-- Delete Button --}}
                        <button onclick="deleteDocument({{ $document->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <div class="mx-auto h-24 w-24 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Belum Ada Dokumen</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $record->name }} belum mengupload dokumen apapun.
            </p>
            <div class="mt-6">
                <button onclick="openUploadModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Upload Dokumen Pertama
                </button>
            </div>
        </div>
    @endif
</div>

{{-- JavaScript untuk Actions --}}
<script>
function verifyDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin memverifikasi dokumen ini?')) {
        // AJAX call untuk verifikasi
        fetch(`/hrd/employee-documents/${documentId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh halaman
            } else {
                alert('Gagal memverifikasi dokumen: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }
}

function unverifyDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin membatalkan verifikasi dokumen ini?')) {
        // AJAX call untuk batal verifikasi
        fetch(`/hrd/employee-documents/${documentId}/unverify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh halaman
            } else {
                alert('Gagal membatalkan verifikasi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }
}

function deleteDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.')) {
        // AJAX call untuk hapus dokumen
        fetch(`/hrd/employee-documents/${documentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh halaman
            } else {
                alert('Gagal menghapus dokumen: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }
}

function openUploadModal() {
    // Trigger click pada tombol upload di header
    document.querySelector('[data-upload-trigger]')?.click();
}
</script>