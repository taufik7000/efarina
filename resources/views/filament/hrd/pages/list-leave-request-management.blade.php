{{-- resources/views/filament/hrd/pages/list-leave-request-management.blade.php --}}

<x-filament-panels::page>
    <div class="leave-management-wrapper space-y-6" x-data="{ 
        showRejectModal: false, 
        selectedRequest: null, 
        rejectionReason: '',
        selectedFilter: '{{ $selectedFilter }}'
    }">
        
        {{-- Header Statistics Cards - ShadCN Style --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            {{-- Total Pengajuan --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengajuan</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['total'] }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 dark:bg-blue-950/50 rounded-md">
                        <x-heroicon-o-document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Menunggu</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['pending'] }}</h3>
                    </div>
                    <div class="p-2 bg-yellow-50 dark:bg-yellow-950/50 rounded-md">
                        <x-heroicon-o-clock class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            {{-- Approved --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disetujui</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['approved'] }}</h3>
                    </div>
                    <div class="p-2 bg-green-50 dark:bg-green-950/50 rounded-md">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            {{-- Rejected --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ditolak</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['rejected'] }}</h3>
                    </div>
                    <div class="p-2 bg-red-50 dark:bg-red-950/50 rounded-md">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>

            {{-- Hari Ini --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Hari Ini</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['today'] }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 dark:bg-blue-950/50 rounded-md">
                        <x-heroicon-o-calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            {{-- Bulan Ini --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ $statistics['this_month'] }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 dark:bg-blue-950/50 rounded-md">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Tabs - Mobile Optimized --}}
        <div class="bg-white dark:bg-gray-950 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800">
            {{-- Mobile Tab Selector --}}
            <div class="block sm:hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <label for="mobile-tabs" class="sr-only">Select a tab</label>
                    <select id="mobile-tabs" 
                            x-model="selectedFilter" 
                            @change="$wire.filterRequests(selectedFilter)"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending">Menunggu Persetujuan ({{ $statistics['pending'] }})</option>
                        <option value="approved">Disetujui ({{ $statistics['approved'] }})</option>
                        <option value="rejected">Ditolak ({{ $statistics['rejected'] }})</option>
                        <option value="all">Semua ({{ $statistics['total'] }})</option>
                    </select>
                </div>
            </div>

            {{-- Desktop Tabs --}}
            <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700">
                <nav class="flex space-x-0" aria-label="Tabs">
                    <button wire:click="filterRequests('pending')" 
                            class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                            :class="selectedFilter === 'pending' ? 
                                'border-yellow-500 text-yellow-600 bg-yellow-50 dark:bg-yellow-950/20 dark:text-yellow-400' : 
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                        <div class="flex items-center justify-center space-x-2">
                            <x-heroicon-o-clock class="h-4 w-4" />
                            <span class="hidden md:inline">Menunggu</span>
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-yellow-500 rounded-full">
                                {{ $statistics['pending'] }}
                            </span>
                        </div>
                    </button>
                    
                    <button wire:click="filterRequests('approved')" 
                            class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                            :class="selectedFilter === 'approved' ? 
                                'border-green-500 text-green-600 bg-green-50 dark:bg-green-950/20 dark:text-green-400' : 
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                        <div class="flex items-center justify-center space-x-2">
                            <x-heroicon-o-check-circle class="h-4 w-4" />
                            <span class="hidden md:inline">Disetujui</span>
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-500 rounded-full">
                                {{ $statistics['approved'] }}
                            </span>
                        </div>
                    </button>
                    
                    <button wire:click="filterRequests('rejected')" 
                            class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                            :class="selectedFilter === 'rejected' ? 
                                'border-red-500 text-red-600 bg-red-50 dark:bg-red-950/20 dark:text-red-400' : 
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                        <div class="flex items-center justify-center space-x-2">
                            <x-heroicon-o-x-circle class="h-4 w-4" />
                            <span class="hidden md:inline">Ditolak</span>
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                                {{ $statistics['rejected'] }}
                            </span>
                        </div>
                    </button>
                    
                    <button wire:click="filterRequests('all')" 
                            class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                            :class="selectedFilter === 'all' ? 
                                'border-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-950/20 dark:text-blue-400' : 
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                        <div class="flex items-center justify-center space-x-2">
                            <x-heroicon-o-document-text class="h-4 w-4" />
                            <span class="hidden md:inline">Semua</span>
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-500 rounded-full">
                                {{ $statistics['total'] }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Content List --}}
            <div class="p-4 sm:p-6">
                @if($pendingRequests->count() > 0)
                    <div class="space-y-4">
                        @foreach($pendingRequests as $request)
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-200">
                                {{-- Request Header --}}
                                <div class="p-6">
                                    <div class="flex items-start justify-between">
                                        {{-- Left Side - Employee Info --}}
                                        <div class="flex items-start space-x-4">
                                            {{-- Avatar --}}
                                            <div class="flex-shrink-0">
                                                <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                    {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            
                                            {{-- Employee Details --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $request->user->name }}
                                                    </h3>
                                                    @if($request->user->jabatan)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            {{ $request->user->jabatan->nama_jabatan }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Leave Type Badge --}}
                                                <div class="flex items-center space-x-3 mt-2">
                                                    @php
                                                        $leaveTypeColors = [
                                                            'Cuti Tahunan' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                            'Cuti Sakit' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                            'Cuti Alasan Penting' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                            'Cuti Melahirkan' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $leaveTypeColors[$request->leave_type] ?? 'bg-gray-100 text-gray-800' }}">
                                                        <x-heroicon-s-calendar class="h-4 w-4 mr-2" />
                                                        {{ $request->leave_type }}
                                                    </span>
                                                    
                                                    {{-- Duration --}}
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $request->total_days }} hari
                                                    </span>
                                                </div>
                                                
                                                {{-- Dates --}}
                                                <div class="flex items-center space-x-2 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-calendar class="h-4 w-4" />
                                                    <span>{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</span>
                                                </div>
                                                
                                                {{-- Replacement Info --}}
                                                @if($request->replacement_user_id)
                                                    <div class="flex items-center space-x-2 mt-2">
                                                        <x-heroicon-o-user-plus class="h-4 w-4 text-gray-400" />
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                                            Pengganti: <span class="font-medium">{{ $request->replacementUser->name }}</span>
                                                        </span>
                                                        @php
                                                            $replacementStatusColors = [
                                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                                'approved' => 'bg-green-100 text-green-800',
                                                                'rejected' => 'bg-red-100 text-red-800',
                                                            ];
                                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $replacementStatusColors[$request->replacement_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst($request->replacement_status) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                
                                                {{-- Reason --}}
                                                @if($request->reason)
                                                    <div class="mt-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                                            <span class="font-medium">Alasan:</span> {{ $request->reason }}
                                                        </p>
                                                    </div>
                                                @endif
                                                
                                                {{-- Rejection Reason (if rejected) --}}
                                                @if($request->status === 'rejected' && $request->rejection_reason)
                                                    <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                                        <p class="text-sm text-red-700 dark:text-red-300">
                                                            <span class="font-medium">Alasan Penolakan:</span> {{ $request->rejection_reason }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Right Side - Status & Actions --}}
                                        <div class="flex flex-col items-end space-y-3">
                                            {{-- Status Badge --}}
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', 'Menunggu'],
                                                    'approved' => ['bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 'Disetujui'],
                                                    'rejected' => ['bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', 'Ditolak'],
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusConfig[$request->status][0] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusConfig[$request->status][1] ?? ucfirst($request->status) }}
                                            </span>
                                            
                                            {{-- Action Buttons (only for pending) --}}
                                            @if($request->status === 'pending')
                                                <div class="flex items-center space-x-2">
                                                    {{-- Approve Button --}}
                                                    <button wire:click="approveRequest({{ $request->id }})" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                                            @if($request->replacement_user_id && $request->replacement_status !== 'approved') disabled title="Pengganti belum menyetujui" @endif>
                                                        <x-heroicon-s-check class="h-4 w-4 mr-1" />
                                                        Setujui
                                                    </button>
                                                    
                                                    {{-- Reject Button --}}
                                                    <button @click="selectedRequest = {{ $request->id }}; showRejectModal = true" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                                        <x-heroicon-s-x-mark class="h-4 w-4 mr-1" />
                                                        Tolak
                                                    </button>
                                                </div>
                                            @endif
                                            
                                            {{-- Timestamp --}}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $request->created_at->format('d M Y H:i') }}
                                            </div>
                                            
                                            {{-- Approved/Rejected by --}}
                                            @if($request->approved_by && $request->approver)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    oleh {{ $request->approver->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-12">
                        <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada pengajuan cuti</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if($selectedFilter === 'pending')
                                Tidak ada pengajuan yang menunggu persetujuan saat ini.
                            @elseif($selectedFilter === 'approved')
                                Belum ada pengajuan yang disetujui.
                            @elseif($selectedFilter === 'rejected')
                                Belum ada pengajuan yang ditolak.
                            @else
                                Belum ada pengajuan cuti yang dibuat.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Reject Modal --}}
        <div x-show="showRejectModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Tolak Pengajuan Cuti</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Berikan alasan penolakan untuk pengajuan cuti ini.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <textarea x-model="rejectionReason" 
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-gray-100"
                                              rows="4" 
                                              placeholder="Masukkan alasan penolakan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="$wire.rejectRequest(selectedRequest, rejectionReason); showRejectModal = false; rejectionReason = ''" 
                                type="button" 
                                :disabled="!rejectionReason.trim()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Tolak Pengajuan
                        </button>
                        <button @click="showRejectModal = false; rejectionReason = ''" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notification Script --}}
    <script>
        window.addEventListener('notify', event => {
            const [type, message] = event.detail;
            
            // Filament notification
            window.$wireui?.notify({
                title: type === 'success' ? 'Berhasil' : 'Error',
                description: message,
                icon: type === 'success' ? 'check' : 'x-mark',
            });
            
            // Fallback notification
            if (!window.$wireui) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                }`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => document.body.removeChild(notification), 300);
                }, 3000);
            }
        });
    </script>

    {{-- Custom Styles --}}
    <style>
        [x-cloak] { display: none !important; }
        
        .leave-management-wrapper .hover\:shadow-md:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        @media (prefers-color-scheme: dark) {
            .leave-management-wrapper .hover\:shadow-md:hover {
                box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.1), 0 2px 4px -1px rgba(255, 255, 255, 0.06);
            }
        }
        
        .transition-colors {
            transition-property: color, background-color, border-color;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }
    </style>
</x-filament-panels::page>