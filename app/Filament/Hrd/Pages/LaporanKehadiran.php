<?php

namespace App\Filament\Hrd\Pages;

use App\Models\User;
use App\Services\HolidayService;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class LaporanKehadiran extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.hrd.pages.laporan-kehadiran';
    protected static ?string $navigationGroup = 'Manajemen Absensi';
    protected static ?string $navigationLabel = 'Laporan Kehadiran Bulanan';

    protected static ?int $navigationSort = 5 ;
    protected static ?string $title = 'Laporan Kehadiran Bulanan';

    public $bulan;
    public $tahun;
    public $reportData = [];
    public $summary = [];
    protected ?HolidayService $holidayService = null;

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->generateReport();
    }

    protected function getHolidayService(): HolidayService
    {
        if ($this->holidayService === null) {
            $this->holidayService = new HolidayService();
        }
        return $this->holidayService;
    }

    public function generateReport(): void
    {
        $startDate = Carbon::create($this->tahun, $this->bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $holidays = $this->getHolidayService()->getHolidays($this->tahun);

        // Hitung total hari kerja efektif dalam sebulan
        $workingDays = $startDate->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            return !$date->isSunday() && !isset($holidays[$date->format('Y-m-d')]);
        }, $endDate);

        // Ambil semua user kecuali Direktur dan Admin
        $users = User::with(['jabatan', 'kehadiran' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }])
        ->whereHas('roles', function ($query) {
            $query->whereNotIn('name', ['Direktur', 'Admin']);
        })
        ->get();

        $data = [];
        $totalSummary = array_fill_keys(['hadir', 'terlambat', 'cuti', 'sakit', 'izin', 'kompensasi', 'absen'], 0);

        foreach ($users as $user) {
            $statusCounts = $user->kehadiran->countBy('status')->all();

            $hadir = ($statusCounts['Tepat Waktu'] ?? 0) + ($statusCounts['Terlambat'] ?? 0);
            $terlambat = $statusCounts['Terlambat'] ?? 0;
            $cuti = $statusCounts['Cuti'] ?? 0;
            $sakit = $statusCounts['Sakit'] ?? 0;
            $izin = $statusCounts['Izin'] ?? 0;
            $kompensasi = $statusCounts['Kompensasi Libur'] ?? 0;

            // Absen dihitung dari sisa hari kerja efektif
            $totalNonAbsen = $hadir + $cuti + $sakit + $izin + $kompensasi;
            $absen = max(0, $workingDays - $totalNonAbsen);
            
            // Kalkulasi persentase kehadiran
            $attendanceRate = $workingDays > 0 ? round(($hadir / $workingDays) * 100) : 0;

            $data[] = [
                'name' => $user->name,
                'jabatan' => $user->jabatan->nama_jabatan ?? 'N/A',
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'cuti' => $cuti,
                'sakit' => $sakit,
                'izin' => $izin,
                'kompensasi' => $kompensasi,
                'absen' => $absen,
                'attendance_rate' => $attendanceRate,
            ];

            // Akumulasi summary total
            $totalSummary['hadir'] += $hadir;
            $totalSummary['terlambat'] += $terlambat;
            $totalSummary['cuti'] += $cuti;
            $totalSummary['sakit'] += $sakit;
            $totalSummary['izin'] += $izin;
            $totalSummary['kompensasi'] += $kompensasi;
            $totalSummary['absen'] += $absen;
        }

        $this->reportData = $data;
        $this->summary = $totalSummary;
    }
}