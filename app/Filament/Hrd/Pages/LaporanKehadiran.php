<?php

namespace App\Filament\Hrd\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Kehadiran;
use Carbon\Carbon;

class LaporanKehadiran extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Absensi Karyawan';

    protected static ?string $navigationLabel = 'Laporan Bulanan';

    protected static string $view = 'filament.hrd.pages.laporan-kehadiran';

    protected static ?int $navigationSort = 2;
    
    public ?int $bulan;
    public ?int $tahun;

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    protected function getViewData(): array
    {
        $tanggalReferensi = Carbon::create($this->tahun, $this->bulan);
        $jumlahHari = $tanggalReferensi->daysInMonth;
        $users = User::orderBy('name')->get();
        
        $dataKehadiran = Kehadiran::whereYear('tanggal', $this->tahun)
                                  ->whereMonth('tanggal', $this->bulan)
                                  ->get()
                                  ->groupBy('user_id');

        $laporan = [];
        foreach ($users as $user) {
            $kehadiranUser = $dataKehadiran->get($user->id, collect())->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->day;
            });
            
            $summary = ['hadir' => 0, 'terlambat' => 0, 'absen' => 0];
            $days = [];

            for ($i = 1; $i <= $jumlahHari; $i++) {
                if ($kehadiranUser->has($i)) {
                    $kehadiranHariIni = $kehadiranUser->get($i);
                    $status = $kehadiranHariIni->status === 'Terlambat' ? 'T' : 'H';
                    if ($status === 'H') $summary['hadir']++;
                    if ($status === 'T') $summary['terlambat']++;
                    $days[$i] = $status;
                } else {
                    $hari = Carbon::create($this->tahun, $this->bulan, $i);
                    
                    // --- PERUBAHAN DI SINI ---
                    // Logika diubah untuk hanya memeriksa hari Minggu
                    if ($hari->isSunday()) {
                        $days[$i] = 'L'; // 'L' untuk Libur
                    } else {
                        // Jika bukan hari Minggu dan tidak ada data, dianggap absen (jika tanggal sudah lewat)
                        if ($hari->isPast() && !$hari->isToday()) {
                            $days[$i] = 'A'; // 'A' untuk Absen
                            $summary['absen']++;
                        } else {
                            $days[$i] = '-';
                        }
                    }
                }
            }
            
            $laporan[] = [
                'nama' => $user->name,
                'days' => $days,
                'summary' => $summary,
            ];
        }

        return [
            'laporan' => $laporan,
            'jumlahHari' => $jumlahHari,
            'namaBulan' => $tanggalReferensi->translatedFormat('F'),
        ];
    }
}