<?php

namespace App\Filament\Hrd\Resources\KehadiranResource\Pages;

use App\Filament\Hrd\Resources\KehadiranResource;
use Filament\Resources\Pages\Page;
use App\Models\User;
use App\Models\Kehadiran;
use Carbon\Carbon;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ViewKehadiran extends Page
{
    use InteractsWithRecord;

    protected static string $resource = KehadiranResource::class;
    protected static string $view = 'filament.hrd.pages.view-kehadiran';

    public ?int $bulan;
    public ?int $tahun;

    public function mount(int | string $record): void
    {
        $this->record = User::findOrFail($record);
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function getTitle(): string
    {
        return 'Laporan Bulanan: ' . $this->record->name;
    }
    
    // --- PERBAIKAN DI SINI ---
    // Mengubah visibilitas menjadi public
    public function getBreadcrumbs(): array
    {
        return [
            KehadiranResource::getUrl('index') => 'Kehadiran Hari Ini',
            $this->getTitle(),
        ];
    }

    protected function getViewData(): array
    {
        $tanggalAwal = Carbon::create($this->tahun, $this->bulan, 1);
        $jumlahHari = $tanggalAwal->daysInMonth;
        
        $dataKehadiran = Kehadiran::where('user_id', $this->record->id)
                                  ->whereYear('tanggal', $this->tahun)
                                  ->whereMonth('tanggal', $this->bulan)
                                  ->get()
                                  ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->day);

        $summary = ['hadir' => 0, 'terlambat' => 0, 'absen' => 0];
        $days = [];

        // Tambahkan hari kosong di awal untuk alignment kalender
        // Carbon dayOfWeek: 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
        $hariAwalBulan = $tanggalAwal->dayOfWeek;
        for ($i = 0; $i < $hariAwalBulan; $i++) {
            $days[] = null;
        }

        // Isi data kehadiran untuk setiap hari
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $hari = Carbon::create($this->tahun, $this->bulan, $i);
            $dayData = [];

            if ($dataKehadiran->has($i)) {
                $kehadiranHariIni = $dataKehadiran->get($i);
                $status = $kehadiranHariIni->status === 'Terlambat' ? 'T' : 'H';
                if ($status === 'H') $summary['hadir']++;
                if ($status === 'T') $summary['terlambat']++;
                $dayData = ['status' => $status, 'jam_masuk' => Carbon::parse($kehadiranHariIni->jam_masuk)->format('H:i')];
            } else {
                if ($hari->isSunday()) {
                    $dayData = ['status' => 'L', 'jam_masuk' => 'Libur'];
                } else {
                    if ($hari->isPast() && !$hari->isToday()) {
                        $dayData = ['status' => 'A', 'jam_masuk' => 'Absen'];
                        $summary['absen']++;
                    } else {
                        $dayData = ['status' => '-', 'jam_masuk' => '-'];
                    }
                }
            }
            $days[] = $dayData;
        }

        return [
            // Membagi array hari menjadi kelompok 7 hari (per minggu)
            'weeks' => array_chunk($days, 7),
            'summary' => $summary,
            'namaBulan' => $tanggalAwal->translatedFormat('F'),
        ];
    }
}