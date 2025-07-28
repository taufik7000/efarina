{{-- resources/views/filament/hrd/pages/manage-employee-documents.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Kelola Dokumen - {{ $record->name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ $record->jabatan?->nama_jabatan ?? 'No position' }} 
                        @if($record->jabatan?->divisi)
                            • {{ $record->jabatan->divisi->nama_divisi }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Dokumen</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $record->employeeDocuments->count() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Terverifikasi</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $record->getVerifiedDocumentsCount() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dokumen Karyawan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola dan verifikasi dokumen-dokumen yang diupload oleh karyawan
                </p>
            </div>
            
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>

        {{-- Navigation Actions --}}
        <div class="flex justify-between items-center">
            <a href="{{ \App\Filament\Hrd\Resources\EmployeeProfileResource::getUrl('view', ['record' => $record]) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                Kembali ke Profile
            </a>
            
            <a href="{{ \App\Filament\Hrd\Resources\EmployeeProfileResource::getUrl('edit', ['record' => $record]) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                Edit Profile
            </a>
        </div>
    </div>
</x-filament-panels::page>