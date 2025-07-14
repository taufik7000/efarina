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

    {{-- Laporan --}}
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold mb-4">Laporan: {{ $this->record->name }} - {{ $namaBulan }} {{ $this->tahun }}</h2>
            {{-- Summary Box --}}
            <div class="flex gap-4 text-center">
                <div class="px-3 py-2 bg-green-100 rounded-lg dark:bg-green-900">
                    <span class="text-xs font-bold text-green-700 dark:text-green-300">HADIR</span>
                    <p class="text-lg font-extrabold text-green-800 dark:text-green-200">{{ $summary['hadir'] }}</p>
                </div>
                <div class="px-3 py-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                    <span class="text-xs font-bold text-yellow-700 dark:text-yellow-300">TERLAMBAT</span>
                    <p class="text-lg font-extrabold text-yellow-800 dark:text-yellow-200">{{ $summary['terlambat'] }}</p>
                </div>
                 <div class="px-3 py-2 bg-red-100 rounded-lg dark:bg-red-900">
                    <span class="text-xs font-bold text-red-700 dark:text-red-300">ABSEN</span>
                    <p class="text-lg font-extrabold text-red-800 dark:text-red-200">{{ $summary['absen'] }}</p>
                </div>
            </div>
        </div>
        <hr class="my-4">
        
        {{-- Keterangan --}}
        <div class="flex items-center gap-4 mb-4 text-sm">
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-green-200"></div> H: Hadir</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-yellow-200"></div> T: Terlambat</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-red-200"></div> A: Absen</span>
            <span class="flex items-center gap-1"><div class="w-4 h-4 bg-gray-200"></div> L: Libur</span>
        </div>

        {{-- Kalender Grid --}}
        <div class="grid grid-cols-7 gap-1">
            {{-- Header Hari --}}
            @foreach(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $namaHari)
                <div class="p-2 font-bold text-center text-gray-600 dark:text-gray-300">{{ $namaHari }}</div>
            @endforeach

            {{-- Body Kalender --}}
            @foreach ($weeks as $week)
                @foreach ($week as $day)
                    @if ($day)
                        @php
                            $color = match($day['status']) {
                                'H' => 'bg-green-100 dark:bg-green-800 border-green-200',
                                'T' => 'bg-yellow-100 dark:bg-yellow-800 border-yellow-200',
                                'A' => 'bg-red-100 dark:bg-red-800 border-red-200',
                                'L' => 'bg-gray-100 dark:bg-gray-700 border-gray-200',
                                default => 'bg-gray-50 dark:bg-gray-900 border-gray-200',
                            };
                        @endphp
                        <div class="p-2 border rounded-lg h-24 {{ $color }}">
                            <p class="font-bold text-base">{{ $day['status'] }}</p>
                            <p class="text-xs">{{ $day['jam_masuk'] }}</p>
                        </div>
                    @else
                        {{-- Sel kosong untuk alignment --}}
                        <div class="border border-transparent"></div>
                    @endif
                @endforeach
                 {{-- Jika 1 baris tidak penuh 7 hari, isi dengan sel kosong --}}
                @for ($i = count($week); $i < 7; $i++)
                    <div class="border border-transparent"></div>
                @endfor
            @endforeach
        </div>
    </div>
</x-filament-panels::page>