<x-filament-panels::page>
    {{-- Filter Bulan dan Tahun --}}
    <div class="flex items-center gap-4 p-4 mb-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="w-1/4">
            <label for="bulan" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bulan</label>
            <select id="bulan" wire:model.live="bulan" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div class="w-1/4">
            <label for="tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tahun</label>
            <select id="tahun" wire:model.live="tahun" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600">
                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Header dengan Info Karyawan dan Summary --}}
    <div class="p-6 mb-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ auth()->user()->name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ auth()->user()->jabatan?->nama_jabatan ?? 'Tidak ada jabatan' }} - {{ $namaBulan }} {{ $this->tahun }}
                </p>
            </div>
            {{-- Quick Stats --}}
            <div class="grid grid-cols-3 gap-2 text-center sm:grid-cols-5 sm:gap-4">
                <div class="px-3 py-2 bg-green-100 rounded-lg dark:bg-green-900">
                    <span class="text-xs font-bold text-green-700 dark:text-green-300">HADIR</span>
                    <p class="text-lg font-extrabold text-green-800 dark:text-green-200">{{ $summary['hadir'] }}</p>
                </div>
                <div class="px-3 py-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                    <span class="text-xs font-bold text-yellow-700 dark:text-yellow-300">TERLAMBAT</span>
                    <p class="text-lg font-extrabold text-yellow-800 dark:text-yellow-200">{{ $summary['terlambat'] }}</p>
                </div>
                <div class="px-3 py-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                    <span class="text-xs font-bold text-blue-700 dark:text-blue-300">CUTI</span>
                    <p class="text-lg font-extrabold text-blue-800 dark:text-blue-200">{{ $summary['cuti'] }}</p>
                </div>
                 <div class="px-3 py-2 bg-indigo-100 rounded-lg dark:bg-indigo-900">
                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300">KOMPENSASI</span>
                    <p class="text-lg font-extrabold text-indigo-800 dark:text-indigo-200">{{ $summary['kompensasi'] }}</p>
                </div>
                <div class="px-3 py-2 bg-red-100 rounded-lg dark:bg-red-900">
                    <span class="text-xs font-bold text-red-700 dark:text-red-300">ABSEN</span>
                    <p class="text-lg font-extrabold text-red-800 dark:text-red-200">{{ $summary['absen'] }}</p>
                </div>
            </div>
        </div>
        {{-- Statistics Row --}}
        <div class="grid grid-cols-1 gap-4 mt-6 text-sm md:grid-cols-2 lg:grid-cols-4">
           <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Tingkat Kehadiran</span>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['attendance_rate'] }}%</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Hari Kerja Efektif</span>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['working_days'] }} hari</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Cuti Terpakai Bulan Ini</span>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['leave_quota_used'] }}/{{ $statistics['leave_quota_total'] }} hari</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Sisa Kuota Cuti</span>
                <p class="text-lg font-bold {{ $statistics['leave_quota_remaining'] <= 1 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $statistics['leave_quota_remaining'] }} hari
                </p>
            </div>
        </div>
    </div>

    {{-- Calendar View --}}
    <div class="p-6 mb-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Kalender Kehadiran</h3>
        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 mb-6 text-xs">
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-green-200 border border-green-300 rounded"></div> H: Hadir</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-yellow-200 border border-yellow-300 rounded"></div> T: Terlambat</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-red-200 border border-red-300 rounded"></div> A: Absen</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-blue-200 border border-blue-300 rounded"></div> C: Cuti</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-orange-200 border border-orange-300 rounded"></div> S: Sakit</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-purple-200 border border-purple-300 rounded"></div> I: Izin</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-indigo-200 border border-indigo-300 rounded"></div> K: Kompensasi</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-gray-200 border border-gray-300 rounded"></div> L: Libur</span>
        </div>

        {{-- Calendar Grid (READ ONLY) --}}
        <div class="grid grid-cols-7 gap-1">
            @foreach(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $namaHari)
                <div class="p-2 font-bold text-center text-gray-600 rounded dark:text-gray-300 bg-gray-50 dark:bg-gray-700">
                    {{ $namaHari }}
                </div>
            @endforeach

            @foreach ($weeks as $week)
                @foreach ($week as $day)
                    @if ($day)
                        @php
                            $colorClass = match($day['status']) {
                                'H' => 'bg-green-100 dark:bg-green-800 border-green-300',
                                'T' => 'bg-yellow-100 dark:bg-yellow-800 border-yellow-300',
                                'A' => 'bg-red-100 dark:bg-red-800 border-red-300',
                                'C' => 'bg-blue-100 dark:bg-blue-800 border-blue-300',
                                'S' => 'bg-orange-100 dark:bg-orange-800 border-orange-300',
                                'I' => 'bg-purple-100 dark:bg-purple-800 border-purple-300',
                                'K' => 'bg-indigo-100 dark:bg-indigo-800 border-indigo-300',
                                'L' => 'bg-gray-200 dark:bg-gray-700 border-gray-300',
                                default => 'bg-gray-50 dark:bg-gray-900 border-gray-200',
                            };
                        @endphp
                        
<div title="{{ $day['status_full'] }}" class="relative p-2 border rounded-lg h-auto flex flex-col justify-between {{ $colorClass }}">
    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $day['date'] }}</div>
    <div class="flex-grow flex items-center justify-center">
        <p class="text-2xl font-extrabold">{{ $day['status'] }}</p>
    </div>
    <div class="text-center h-8">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $day['status_full'] }}</p>
        @if($day['status'] === 'L' && $day['holiday_name'])
            <p class="text-[10px] leading-tight font-semibold text-red-600 dark:text-red-400 break-words">{{ $day['holiday_name'] }}</p>
        @endif
    </div>
</div>
                    @else
                        <div class="border border-transparent h-28"></div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Ringkasan Bulanan</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Hadir:</span>
                    <span class="font-semibold text-green-600">{{ $summary['hadir'] + $summary['terlambat'] }} hari</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Cuti/Izin:</span>
                    <span class="font-semibold text-blue-600">{{ $summary['cuti'] + $summary['sakit'] + $summary['izin'] }} hari</span>
                </div>
                 <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Kompensasi Digunakan:</span>
                    <span class="font-semibold text-indigo-600">{{ $summary['kompensasi'] }} hari</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Absen:</span>
                    <span class="font-semibold text-red-600">{{ $summary['absen'] }} hari</span>
                </div>
                <hr class="my-2 border-gray-200 dark:border-gray-700">
                <div class="flex justify-between text-lg font-bold">
                    <span class="text-gray-900 dark:text-gray-100">Persentase Kehadiran:</span>
                    <span class="{{ $statistics['attendance_rate'] >= 80 ? 'text-green-600' : ($statistics['attendance_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $statistics['attendance_rate'] }}%
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Status Kuota & Kompensasi</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Sisa Kuota Cuti Tahunan:</span>
                    <span class="font-semibold text-{{ $statistics['leave_quota_remaining'] > 1 ? 'green' : 'red' }}-600">
                        {{ $statistics['leave_quota_remaining'] }} hari
                    </span>
                </div>
            </div>
            <hr class="my-4 border-gray-200 dark:border-gray-700">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Kompensasi Tersedia:</span>
                    <span class="font-semibold text-indigo-600">{{ $compensation_stats['available_days'] }} hari</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Kompensasi Digunakan Bulan Ini:</span>
                    <span class="font-semibold text-indigo-600">{{ $compensation_stats['used_this_month'] }} hari</span>
                </div>
                 @if($compensation_stats['expiring_soon'] > 0)
                <div class="p-3 mt-4 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <x-heroicon-o-exclamation-triangle class="inline w-4 h-4 mr-1"/>
                        <strong>{{ $compensation_stats['expiring_soon'] }} hari</strong> kompensasi akan hangus dalam 30 hari.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>