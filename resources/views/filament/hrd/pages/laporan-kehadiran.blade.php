<x-filament-panels::page>
    {{-- Filter --}}
    <div class="flex items-center gap-4 p-4 mb-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="w-1/4">
            <label for="bulan" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bulan</label>
            <select id="bulan" wire:model.live="bulan" wire:change="generateReport" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div class="w-1/4">
            <label for="tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tahun</label>
            <select id="tahun" wire:model.live="tahun" wire:change="generateReport" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600">
                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>
    
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4 lg:grid-cols-7">
        <div class="p-4 bg-green-100 rounded-lg dark:bg-green-900">
            <div class="text-sm font-bold text-green-700 dark:text-green-300">Total Hadir</div>
            <div class="text-2xl font-extrabold text-green-800 dark:text-green-200">{{ $summary['hadir'] }}</div>
        </div>
        <div class="p-4 bg-yellow-100 rounded-lg dark:bg-yellow-900">
            <div class="text-sm font-bold text-yellow-700 dark:text-yellow-300">Total Terlambat</div>
            <div class="text-2xl font-extrabold text-yellow-800 dark:text-yellow-200">{{ $summary['terlambat'] }}</div>
        </div>
        <div class="p-4 bg-blue-100 rounded-lg dark:bg-blue-900">
            <div class="text-sm font-bold text-blue-700 dark:text-blue-300">Total Cuti</div>
            <div class="text-2xl font-extrabold text-blue-800 dark:text-blue-200">{{ $summary['cuti'] }}</div>
        </div>
        <div class="p-4 bg-orange-100 rounded-lg dark:bg-orange-900">
            <div class="text-sm font-bold text-orange-700 dark:text-orange-300">Total Sakit</div>
            <div class="text-2xl font-extrabold text-orange-800 dark:text-orange-200">{{ $summary['sakit'] }}</div>
        </div>
        <div class="p-4 bg-purple-100 rounded-lg dark:bg-purple-900">
            <div class="text-sm font-bold text-purple-700 dark:text-purple-300">Total Izin</div>
            <div class="text-2xl font-extrabold text-purple-800 dark:text-purple-200">{{ $summary['izin'] }}</div>
        </div>
        <div class="p-4 bg-indigo-100 rounded-lg dark:bg-indigo-900">
            <div class="text-sm font-bold text-indigo-700 dark:text-indigo-300">Total Kompensasi</div>
            <div class="text-2xl font-extrabold text-indigo-800 dark:text-indigo-200">{{ $summary['kompensasi'] }}</div>
        </div>
        <div class="p-4 bg-red-100 rounded-lg dark:bg-red-900">
            <div class="text-sm font-bold text-red-700 dark:text-red-300">Total Absen</div>
            <div class="text-2xl font-extrabold text-red-800 dark:text-red-200">{{ $summary['absen'] }}</div>
        </div>
    </div>


    {{-- Tabel Laporan --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                    <th scope="col" class="px-6 py-3">Jabatan</th>
                    <th scope="col" class="px-4 py-3 text-center">Hadir</th>
                    <th scope="col" class="px-4 py-3 text-center">Telat</th>
                    <th scope="col" class="px-4 py-3 text-center">Cuti</th>
                    <th scope="col" class="px-4 py-3 text-center">Sakit</th>
                    <th scope="col" class="px-4 py-3 text-center">Izin</th>
                    <th scope="col" class="px-4 py-3 text-center">Komp</th>
                    <th scope="col" class="px-4 py-3 text-center">Absen</th>
                    <th scope="col" class="px-6 py-3 text-right">Kehadiran (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData as $data)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $data['name'] }}
                        </th>
                        <td class="px-6 py-4">{{ $data['jabatan'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['hadir'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['terlambat'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['cuti'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['sakit'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['izin'] }}</td>
                        <td class="px-4 py-4 text-center">{{ $data['kompensasi'] }}</td>
                        <td class="px-4 py-4 font-bold text-center text-red-500">{{ $data['absen'] }}</td>
                        <td class="px-6 py-4 text-right">
                            <span @class([
                                'px-2 py-1 text-xs font-bold rounded-full',
                                'bg-green-100 text-green-800' => $data['attendance_rate'] >= 85,
                                'bg-yellow-100 text-yellow-800' => $data['attendance_rate'] >= 70 && $data['attendance_rate'] < 85,
                                'bg-red-100 text-red-800' => $data['attendance_rate'] < 70,
                            ])>
                                {{ $data['attendance_rate'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>