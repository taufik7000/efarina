{{-- resources/views/filament/team/pages/view-pengajuan-anggaran.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Status Card --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl text-white p-6 shadow-lg">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold">{{ $record->judul_pengajuan }}</h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white">
                            {{ $this->getStatusLabel() }}
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-6 text-white/80 text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-document-text class="h-4 w-4" />
                            <span>{{ $record->nomor_pengajuan }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user class="h-4 w-4" />
                            <span>{{ $record->createdBy->name }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-calendar class="h-4 w-4" />
                            <span>{{ $record->created_at->format('d M Y') }}</span>
                        </div>
                        @if($record->project)
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-briefcase class="h-4 w-4" />
                                <span>{{ $record->project->nama_project }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-2xl font-bold">Rp {{ number_format($record->total_anggaran, 0, ',', '.') }}</div>
                    <div class="text-white/70 text-sm">Total Anggaran</div>
                    
                    {{-- Deadline Warning --}}
                    @if($this->isOverdue())
                        <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-500 text-white">
                            <x-heroicon-o-exclamation-triangle class="h-3 w-3 mr-1" />
                            Overdue
                        </div>
                    @elseif($this->getDaysUntilNeeded() <= 3 && $this->getDaysUntilNeeded() > 0)
                        <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-500 text-white">
                            <x-heroicon-o-clock class="h-3 w-3 mr-1" />
                            {{ $this->getDaysUntilNeeded() }} hari lagi
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Informasi Pengajuan --}}
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Pengajuan</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Dibutuhkan:</label>
                            <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $record->tanggal_dibutuhkan?->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Project Terkait:</label>
                            <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $record->project?->nama_project ?? 'Tidak terkait project' }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Deskripsi Kebutuhan:</label>
                        <div class="prose dark:prose-invert max-w-none mt-2">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $record->deskripsi }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Justifikasi Bisnis:</label>
                        <div class="prose dark:prose-invert max-w-none mt-2">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $record->justifikasi }}</p>
                        </div>
                    </div>
                </div>

                {{-- Detail Item Anggaran --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                                <x-heroicon-o-currency-dollar class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detail Item Anggaran</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ count($record->detail_items) }} Items
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($record->detail_items as $index => $item)
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-medium dark:bg-blue-900/50 dark:text-blue-400">
                                                {{ $index + 1 }}
                                            </span>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $item['item_name'] ?? $item['nama_item'] ?? 'Item' }}</h4>
                                        </div>
                                        
                                        @if($item['description'] ?? $item['spesifikasi'] ?? false)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $item['description'] ?? $item['spesifikasi'] }}</p>
                                        @endif
                                        
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <div class="flex items-center space-x-1">
                                                <span>Qty:</span>
                                                <span class="font-medium">{{ $item['quantity'] ?? $item['kuantitas'] ?? 1 }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <span>Harga:</span>
                                                <span class="font-medium">Rp {{ number_format($item['unit_price'] ?? $item['harga_satuan'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right ml-4">
                                        <div class="text-xl font-bold text-green-600">
                                            Rp {{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-sm text-gray-500">Total</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">
                                Tidak ada detail item anggaran
                            </div>
                        @endforelse
                    </div>

                    {{-- Total Summary --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Total Anggaran:</span>
                            <span class="text-2xl font-bold text-green-600">Rp {{ number_format($record->total_anggaran, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Approval Notes --}}
                @if($record->redaksi_notes || $record->keuangan_notes)
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Catatan Approval</h3>
                        
                        @if($record->redaksi_notes)
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Catatan Redaksi:</h4>
                                <p class="text-blue-800 dark:text-blue-200 text-sm">{{ $record->redaksi_notes }}</p>
                            </div>
                        @endif

                        @if($record->keuangan_notes)
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <h4 class="font-medium text-green-900 dark:text-green-100 mb-2">Catatan Keuangan:</h4>
                                <p class="text-green-800 dark:text-green-200 text-sm">{{ $record->keuangan_notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status & Timeline --}}
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Status & Timeline</h3>
                    
                    <div class="space-y-4">
                        {{-- Current Status --}}
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full {{ $this->getStatusColor() === 'success' ? 'bg-green-500' : ($this->getStatusColor() === 'danger' ? 'bg-red-500' : ($this->getStatusColor() === 'warning' ? 'bg-yellow-500' : 'bg-blue-500')) }}"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $this->getStatusLabel() }}</p>
                                <p class="text-xs text-gray-500">Status saat ini</p>
                            </div>
                        </div>

                        {{-- Timeline --}}
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Dibuat</p>
                                    <p class="text-xs text-gray-500">{{ $record->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($record->redaksi_approved_at)
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Disetujui Redaksi</p>
                                        <p class="text-xs text-gray-500">{{ $record->redaksi_approved_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($record->keuangan_approved_at)
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Disetujui Keuangan</p>
                                        <p class="text-xs text-gray-500">{{ $record->keuangan_approved_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Summary Info --}}
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ringkasan</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jumlah Item:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ count($record->detail_items) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Anggaran:</span>
                            <span class="font-bold text-green-600">Rp {{ number_format($record->total_anggaran, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dibuat:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d M Y') }}</span>
                        </div>
                        @if($record->tanggal_dibutuhkan)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dibutuhkan:</span>
                                <span class="font-medium {{ $this->isOverdue() ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $record->tanggal_dibutuhkan->format('d M Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>