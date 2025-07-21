{{-- resources/views/filament/hrd/widgets/employee-profile-alerts.blade.php --}}

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-500 mr-2"/>
                🚨 Perhatian HRD
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- Profile Belum Lengkap --}}
            @if($incompleteProfiles->count() > 0)
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/30 border-l-4 border-yellow-400 rounded-lg p-4 shadow-sm">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400"/>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-yellow-800 dark:text-yellow-200">
                            Profile Belum Lengkap 
                            <span class="bg-yellow-200 dark:bg-yellow-700 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-xs font-medium ml-2">
                                {{ $incompleteProfiles->count() }}
                            </span>
                        </h3>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                            Karyawan dengan profile tidak lengkap perlu segera ditindaklanjuti
                        </p>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    @foreach($incompleteProfiles as $user)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <img class="h-8 w-8 rounded-full object-cover" 
                                 src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=F59E0B&background=FEF3C7" 
                                 alt="{{ $user->name }}">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->jabatan?->nama_jabatan ?? 'Jabatan belum diatur' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->getProfileCompletionPercentage() >= 50 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $user->getProfileCompletionPercentage() }}%
                            </span>
                            <a href="{{ route('filament.hrd.resources.employee-profiles.edit', $user) }}" 
                               class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-200">
                                <x-heroicon-o-pencil class="h-4 w-4"/>
                            </a>
                        </div>
                    </div>
                    @endforeach

                    @if(\App\Models\User::withIncompleteProfile()->count() > 5)
                    <div class="text-center py-2">
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 italic">
                            Dan {{ \App\Models\User::withIncompleteProfile()->count() - 5 }} karyawan lainnya...
                        </p>
                    </div>
                    @endif
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('filament.hrd.resources.employee-profiles.index', ['activeTab' => 'incomplete']) }}" 
                       class="inline-flex items-center text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-500 dark:hover:text-yellow-300">
                        Lihat Semua Profile Belum Lengkap
                        <x-heroicon-o-arrow-right class="ml-1 h-4 w-4"/>
                    </a>
                    <button onclick="sendBulkReminder('incomplete')" 
                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-200 hover:bg-yellow-300 dark:bg-yellow-700 dark:text-yellow-200 dark:hover:bg-yellow-600">
                        <x-heroicon-o-bell class="h-3 w-3 mr-1"/>
                        Kirim Reminder
                    </button>
                </div>
            </div>
            @endif

            {{-- Dokumen Belum Diverifikasi --}}
            @if($unverifiedDocuments->count() > 0)
            <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/30 border-l-4 border-red-400 rounded-lg p-4 shadow-sm">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="h-6 w-6 text-red-600 dark:text-red-400"/>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">
                            Dokumen Belum Diverifikasi
                            <span class="bg-red-200 dark:bg-red-700 text-red-800 dark:text-red-200 px-2 py-1 rounded-full text-xs font-medium ml-2">
                                {{ $unverifiedDocuments->count() }}
                            </span>
                        </h3>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-1">
                            Dokumen yang perlu diverifikasi oleh HRD
                        </p>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    @foreach($unverifiedDocuments as $document)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-document-text class="h-8 w-8 text-red-500"/>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $document->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $document->document_type_name }} • {{ $document->uploaded_time_ago }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Pending
                            </span>
                            <a href="{{ route('filament.hrd.resources.employee-profiles.documents', $document->user) }}" 
                               class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                                <x-heroicon-o-eye class="h-4 w-4"/>
                            </a>
                        </div>
                    </div>
                    @endforeach

                    @if(\App\Models\EmployeeDocument::unverified()->count() > 5)
                    <div class="text-center py-2">
                        <p class="text-xs text-red-600 dark:text-red-400 italic">
                            Dan {{ \App\Models\EmployeeDocument::unverified()->count() - 5 }} dokumen lainnya...
                        </p>
                    </div>
                    @endif
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('filament.hrd.resources.employee-profiles.index', ['activeTab' => 'unverified_docs']) }}" 
                       class="inline-flex items-center text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-500 dark:hover:text-red-300">
                        Verifikasi Dokumen Sekarang
                        <x-heroicon-o-arrow-right class="ml-1 h-4 w-4"/>
                    </a>
                    <button onclick="quickVerifyAll()" 
                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                        <x-heroicon-o-check-circle class="h-3 w-3 mr-1"/>
                        Quick Verify
                    </button>
                </div>
            </div>
            @endif

            {{-- Karyawan Baru --}}
            @if($newEmployees->count() > 0)
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/30 border-l-4 border-blue-400 rounded-lg p-4 shadow-sm">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-user-plus class="h-6 w-6 text-blue-600 dark:text-blue-400"/>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-blue-800 dark:text-blue-200">
                            Karyawan Baru Minggu Ini
                            <span class="bg-blue-200 dark:bg-blue-700 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full text-xs font-medium ml-2">
                                {{ $newEmployees->count() }}
                            </span>
                        </h3>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                            Pastikan mereka melengkapi profile dan upload dokumen
                        </p>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    @foreach($newEmployees as $user)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <img class="h-8 w-8 rounded-full object-cover" 
                                 src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=3B82F6&background=DBEAFE" 
                                 alt="{{ $user->name }}">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Bergabung {{ $user->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($user->hasCompleteProfile())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✅ Lengkap
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    🔄 Setup
                                </span>
                            @endif
                            <a href="{{ route('filament.hrd.resources.employee-profiles.view', $user) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200">
                                <x-heroicon-o-eye class="h-4 w-4"/>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center">
                    <p class="text-xs text-blue-600 dark:text-blue-400 flex items-center">
                        <x-heroicon-o-light-bulb class="h-3 w-3 mr-1"/>
                        Berikan guidance untuk setup profile mereka
                    </p>
                    <button onclick="sendWelcomePackage()" 
                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                        <x-heroicon-o-paper-airplane class="h-3 w-3 mr-1"/>
                        Kirim Welcome Kit
                    </button>
                </div>
            </div>
            @endif

            {{-- Reminder: Profile Photo Missing --}}
            @php
                $missingPhotos = \App\Models\User::whereDoesntHave('employeeDocuments', function($q) {
                    $q->where('document_type', 'foto');
                })->limit(3)->get();
            @endphp
            
            @if($missingPhotos->count() > 0)
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/30 border-l-4 border-purple-400 rounded-lg p-4 shadow-sm">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-camera class="h-6 w-6 text-purple-600 dark:text-purple-400"/>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-purple-800 dark:text-purple-200">
                            Foto Profile Belum Ada
                            <span class="bg-purple-200 dark:bg-purple-700 text-purple-800 dark:text-purple-200 px-2 py-1 rounded-full text-xs font-medium ml-2">
                                {{ \App\Models\User::whereDoesntHave('employeeDocuments', function($q) { $q->where('document_type', 'foto'); })->count() }}
                            </span>
                        </h3>
                        <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">
                            Karyawan yang belum upload foto profile
                        </p>
                    </div>
                </div>

                <div class="flex space-x-2 mb-3">
                    @foreach($missingPhotos as $user)
                    <div class="flex items-center bg-white dark:bg-gray-800 rounded-md p-2 shadow-sm flex-1">
                        <div class="h-6 w-6 bg-purple-200 dark:bg-purple-700 rounded-full flex items-center justify-center mr-2">
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-300">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $user->name }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="text-center">
                    <button onclick="remindForPhotos()" 
                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-purple-600 hover:bg-purple-700">
                        <x-heroicon-o-bell class="h-3 w-3 mr-1"/>
                        Reminder Upload Foto
                    </button>
                </div>
            </div>
            @endif

            {{-- Jika tidak ada alert --}}
            @if($incompleteProfiles->count() === 0 && $unverifiedDocuments->count() === 0 && $newEmployees->count() === 0 && $missingPhotos->count() === 0)
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/30 border-l-4 border-green-400 rounded-lg p-6 shadow-sm text-center">
                <div class="flex justify-center mb-3">
                    <div class="h-16 w-16 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                        <x-heroicon-o-check-circle class="h-10 w-10 text-green-600 dark:text-green-400"/>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-green-800 dark:text-green-200 mb-2">
                    🎉 Semua Up-to-Date!
                </h3>
                <p class="text-sm text-green-700 dark:text-green-300 mb-4">
                    Tidak ada profile atau dokumen yang memerlukan perhatian saat ini.
                </p>
                <div class="flex justify-center space-x-4 text-xs text-green-600 dark:text-green-400">
                    <div class="flex items-center">
                        <x-heroicon-o-users class="h-4 w-4 mr-1"/>
                        {{ \App\Models\User::withCompleteProfile()->count() }} Profile Lengkap
                    </div>
                    <div class="flex items-center">
                        <x-heroicon-o-document-check class="h-4 w-4 mr-1"/>
                        {{ \App\Models\EmployeeDocument::verified()->count() }} Dokumen Terverifikasi
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick Stats Summary --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                    <x-heroicon-o-chart-bar class="h-4 w-4 mr-2 text-gray-500"/>
                    Ringkasan Hari Ini
                </h4>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="p-2">
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Models\User::count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Karyawan</p>
                    </div>
                    <div class="p-2">
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ \App\Models\User::withCompleteProfile()->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Profile Lengkap</p>
                    </div>
                    <div class="p-2">
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ \App\Models\EmployeeDocument::unverified()->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending Verify</p>
                    </div>
                    <div class="p-2">
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ \App\Models\User::where('created_at', '>=', now()->subDays(7))->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan Baru</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- JavaScript untuk aksi cepat --}}
    <script>
        function sendBulkReminder(type) {
            let message = '';
            switch(type) {
                case 'incomplete':
                    message = 'Kirim reminder ke semua karyawan dengan profile belum lengkap?';
                    break;
                default:
                    message = 'Kirim reminder?';
            }
            
            if (confirm(message)) {
                // Implementasi dengan Livewire atau AJAX
                fetch('/hrd/send-bulk-reminder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ type: type })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Reminder berhasil dikirim!');
                        location.reload();
                    } else {
                        alert('❌ Gagal mengirim reminder: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan sistem');
                });
            }
        }

        function quickVerifyAll() {
            if (confirm('⚠️ PERHATIAN!\n\nAnda akan memverifikasi SEMUA dokumen yang pending.\nPastikan Anda sudah mengecek dokumen-dokumen tersebut.\n\nLanjutkan?')) {
                fetch('/hrd/quick-verify-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ ${data.count} dokumen berhasil diverifikasi!`);
                        location.reload();
                    } else {
                        alert('❌ Gagal verifikasi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan sistem');
                });
            }
        }

        function sendWelcomePackage() {
            if (confirm('Kirim welcome package (panduan setup profile) ke semua karyawan baru?')) {
                fetch('/hrd/send-welcome-package', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('📧 Welcome package berhasil dikirim!');
                    } else {
                        alert('❌ Gagal mengirim: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan sistem');
                });
            }
        }

        function remindForPhotos() {
            if (confirm('Kirim reminder untuk upload foto profile?')) {
                fetch('/hrd/remind-photos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('📸 Reminder foto berhasil dikirim!');
                    } else {
                        alert('❌ Gagal mengirim: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan sistem');
                });
            }
        }

        // Auto refresh widget setiap 2 menit
        setInterval(function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('refresh-alerts');
            } else {
                location.reload();
            }
        }, 120000); // 2 menit

        // Intersection Observer untuk lazy loading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        });

        document.querySelectorAll('.space-y-4 > div').forEach(el => {
            observer.observe(el);
        });
    </script>

    {{-- Custom CSS untuk animasi --}}
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .shadow-sm {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .border-l-4 {
            border-left-width: 4px;
        }

        .transition-colors {
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out;
        }

        /* Responsive improvements */
        @media (max-width: 640px) {
            .grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .space-x-2 > * + * {
                margin-left: 0;
                margin-top: 0.5rem;
            }
            
            .flex-wrap {
                flex-direction: column;
            }
        }
    </style>
</x-filament-widgets::widget>