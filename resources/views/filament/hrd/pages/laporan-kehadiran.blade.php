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

    {{-- Tabel Laporan Kehadiran --}}
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        <h2 class="text-xl font-bold mb-4">Laporan Kehadiran Bulan: {{ $namaBulan }} {{ $this->tahun }}</h2>
        
        {{-- Keterangan --}}
        <div class="flex items-center gap-4 mb-4 text-sm">
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-green-200"></div> H: Hadir</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-yellow-200"></div> T: Terlambat</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-red-200"></div> A: Absen</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-gray-200"></div> L: Libur</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 divide-y divide-gray-200 dark:border-gray-700 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Nama Karyawan</th>
                        @for ($i = 1; $i <= $jumlahHari; $i++)
                            <th class="w-10 px-2 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300">{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300 bg-green-100 dark:bg-green-900">Hadir</th>
                        <th class="px-2 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300 bg-yellow-100 dark:bg-yellow-900">Telat</th>
                        <th class="px-2 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300 bg-red-100 dark:bg-red-900">Absen</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($laporan as $data)
                        <tr>
                            <td class="px-2 py-2 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $data['nama'] }}</td>
                            @foreach ($data['days'] as $status)
                                @php
                                    $color = match($status) {
                                        'H' => 'bg-green-200 dark:bg-green-800',
                                        'T' => 'bg-yellow-200 dark:bg-yellow-800',
                                        'A' => 'bg-red-200 dark:bg-red-800',
                                        'L' => 'bg-gray-200 dark:bg-gray-700',
                                        default => '',
                                    };
                                @endphp
                                <td class="text-sm text-center {{ $color }}">{{ $status }}</td>
                            @endforeach
                            <td class="text-sm font-bold text-center bg-green-50 dark:bg-green-900">{{ $data['summary']['hadir'] }}</td>
                            <td class="text-sm font-bold text-center bg-yellow-50 dark:bg-yellow-900">{{ $data['summary']['terlambat'] }}</td>
                            <td class="text-sm font-bold text-center bg-red-50 dark:bg-red-900">{{ $data['summary']['absen'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $jumlahHari + 4 }}" class="px-6 py-4 text-center text-gray-500">Tidak ada data untuk ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>