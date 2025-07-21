{{-- resources/views/filament/hrd/pages/manage-employee-documents.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    {{-- Profile Photo --}}
                    <div class="flex-shrink-0">
                        @php
                            $photo = $record->getDocument('foto');
                            $photoUrl = $photo ? Storage::url($photo->file_path) : null;
                        @endphp
                        <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600" 
                             src="{{ $photoUrl ?: 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                             alt="{{ $record->name }}">
                    </div>
                    
                    {{-- Employee Info --}}
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $record->name }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $record->jabatan?->nama_jabatan }} 
                            @if($record->jabatan?->divisi)
                                • {{ $record->jabatan->divisi->nama_divisi }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            📧 {{ $record->email }}
                            @if($record->employeeProfile?->kontak_darurat_telp)
                                • 📞 {{ $record->employeeProfile->kontak_darurat_telp }}
                            @endif
                        </p>
                    </div>
                </div>
                
                {{-- Profile Completion --}}
                <div class="text-right">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $completionPercentage }}%
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-500">Kelengkapan Profile</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <span>Progress Dokumen</span>
                    <span>{{ $verifiedCount }}/{{ $totalCount }} Terverifikasi</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500 ease-out" 
                         style="width: {{ $totalCount > 0 ? ($verifiedCount / $totalCount) * 100 : 0 }}%">
                    </div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    @if($totalCount === 0)
                        Belum ada dokumen yang diupload
                    @elseif($verifiedCount === $totalCount)
                        🎉 Semua dokumen sudah terverifikasi!
                    @else
                        {{ $totalCount - $verifiedCount }} dokumen menunggu verifikasi
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Documents --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500 rounded-lg shadow-lg">
                        <x-heroicon-o-document-duplicate class="h-6 w-6 text-white"/>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Dokumen</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $totalCount }}</p>
                    </div>
                </div>
            </div>

            {{-- Verified Documents --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500 rounded-lg shadow-lg">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-white"/>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Terverifikasi</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $verifiedCount }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending Verification --}}
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-500 rounded-lg shadow-lg">
                        <x-heroicon-o-clock class="h-6 w-6 text-white"/>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $totalCount - $verifiedCount }}</p>
                    </div>
                </div>
            </div>

            {{-- Profile Completion --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-lg shadow-lg">
                        <x-heroicon-o-user class="h-6 w-6 text-white"/>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Profile</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $completionPercentage }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Required Documents Status --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Status Dokumen Wajib</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $requiredDocs = ['ktp', 'cv', 'kontrak', 'foto'];
                    $docLabels = [
                        'ktp' => 'KTP',
                        'cv' => 'CV/Resume', 
                        'kontrak' => 'Kontrak Kerja',
                        'foto' => 'Foto Profil'
                    ];
                @endphp
                
                @foreach($requiredDocs as $docType)
                    @php
                        $doc = $record->getDocument($docType);
                        $hasDoc = $doc !== null;
                        $isVerified = $hasDoc && $doc->is_verified;
                    @endphp
                    
                    <div class="flex items-center p-3 rounded-lg border {{ $isVerified ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($hasDoc ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
                        <div class="flex-shrink-0">
                            @if($isVerified)
                                <x-heroicon-o-check-circle class="h-8 w-8 text-green-500"/>
                            @elseif($hasDoc)
                                <x-heroicon-o-clock class="h-8 w-8 text-yellow-500"/>
                            @else
                                <x-heroicon-o-x-circle class="h-8 w-8 text-gray-400"/>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium {{ $isVerified ? 'text-green-800 dark:text-green-200' : ($hasDoc ? 'text-yellow-800 dark:text-yellow-200' : 'text-gray-700 dark:text-gray-300') }}">
                                {{ $docLabels[$docType] }}
                            </p>
                            <p class="text-xs {{ $isVerified ? 'text-green-600 dark:text-green-400' : ($hasDoc ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-500') }}">
                                @if($isVerified)
                                    ✅ Terverifikasi
                                @elseif($hasDoc)
                                    ⏳ Menunggu verifikasi
                                @else
                                    ❌ Belum diupload
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Documents Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dokumen Karyawan</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola semua dokumen yang telah diupload</p>
            </div>
            
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <x-heroicon-o-bolt class="h-5 w-5 mr-2 text-yellow-500"/>
                Aksi Cepat
            </h3>
            
            <div class="flex flex-wrap gap-3">
                {{-- View Full Profile --}}
                <a href="{{ route('filament.hrd.resources.employee-profiles.view', $record) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                    <x-heroicon-o-user class="w-4 h-4 mr-2"/>
                    Lihat Profile Lengkap
                </a>
                
                {{-- Edit Profile --}}
                <a href="{{ route('filament.hrd.resources.employee-profiles.edit', $record) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-2"/>
                    Edit Profile
                </a>

                {{-- Verify All (if has unverified) --}}
                @if($record->employeeDocuments->where('is_verified', false)->count() > 0)
                <button type="button" 
                        onclick="confirmVerifyAll()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200">
                    <x-heroicon-o-check-circle class="w-4 h-4 mr-2"/>
                    Verifikasi Semua
                </button>
                @endif

                {{-- Send Reminder --}}
                @if(!$record->hasCompleteProfile() || $totalCount === 0)
                <button type="button" 
                        onclick="sendReminder()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                    <x-heroicon-o-bell class="w-4 h-4 mr-2"/>
                    Kirim Reminder
                </button>
                @endif

                {{-- Print Profile --}}
                <button type="button" 
                        onclick="printProfile()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                    <x-heroicon-o-printer class="w-4 h-4 mr-2"/>
                    Print Profile
                </button>
            </div>
        </div>
    </div>

    {{-- JavaScript untuk aksi cepat --}}
    <script>
        function confirmVerifyAll() {
            if (confirm('Apakah Anda yakin ingin memverifikasi semua dokumen yang belum terverifikasi?\n\nTindakan ini akan menandai semua dokumen sebagai terverifikasi.')) {
                // Implementasi dengan Livewire atau AJAX
                window.livewire.dispatch('verify-all-documents');
            }
        }

        function sendReminder() {
            if (confirm('Kirim reminder email kepada {{ $record->name }} untuk melengkapi profile dan upload dokumen?')) {
                // Implementasi kirim reminder
                window.livewire.dispatch('send-reminder', { userId: {{ $record->id }} });
            }
        }

        function printProfile() {
            // Redirect ke URL print/PDF
            window.open('/hrd/employee-profiles/{{ $record->id }}/print', '_blank');
        }

        // Auto refresh setiap 30 detik untuk update real-time
        setInterval(function() {
            window.livewire.dispatch('refresh-table');
        }, 30000);
    </script>

    {{-- Custom Styles --}}
    <style>
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .hover\:scale-105:hover {
            transform: scale(1.05);
        }
        
        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</x-filament-panels::page>