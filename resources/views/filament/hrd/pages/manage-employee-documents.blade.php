{{-- resources/views/filament/hrd/pages/manage-employee-documents.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Debug Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-800">Debug Information</h3>
            <ul class="text-sm text-blue-700 mt-2">
                <li><strong>User ID:</strong> {{ $record->id }}</li>
                <li><strong>User Name:</strong> {{ $record->name }}</li>
                <li><strong>User Email:</strong> {{ $record->email }}</li>
                <li><strong>Has Profile:</strong> {{ $record->employeeProfile ? 'Yes' : 'No' }}</li>
                <li><strong>Documents Count:</strong> {{ $record->employeeDocuments->count() }}</li>
                <li><strong>Current URL:</strong> {{ request()->url() }}</li>
                <li><strong>Auth User:</strong> {{ auth()->user()->name ?? 'Not authenticated' }}</li>
            </ul>
        </div>

        {{-- Simple Header --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900">
                Kelola Dokumen - {{ $record->name }}
            </h2>
            <p class="text-gray-600 mt-2">
                {{ $record->jabatan?->nama_jabatan ?? 'No position' }} 
                @if($record->jabatan?->divisi)
                    • {{ $record->jabatan->divisi->nama_divisi }}
                @endif
            </p>
        </div>

        {{-- Documents Table --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Dokumen Karyawan</h3>
            </div>
            
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>

        {{-- Back to Profile --}}
        <div class="flex justify-between">
            <a href="{{ route('filament.hrd.resources.employee-profiles.view', $record) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                ← Kembali ke Profile
            </a>
            
            <a href="{{ route('filament.hrd.resources.employee-profiles.edit', $record) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Edit Profile
            </a>
        </div>
    </div>
</x-filament-panels::page>