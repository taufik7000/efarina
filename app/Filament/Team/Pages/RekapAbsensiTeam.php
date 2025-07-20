<?php

namespace App\Filament\Team\Pages;

use App\Services\HolidayService;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RekapAbsensiTeam extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Absensi';
    protected static string $view = 'filament.team.pages.rekap-absensi-team';
    protected static ?string $navigationLabel = 'Rekap Absensi Saya';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Rekap Absensi Saya';

    // Properti untuk filter dan data
    public $bulan;
    public $tahun;
    public $weeks = [];
    public $summary = [];
    public $statistics = [];
    public $compensation_stats = [];
    public $namaBulan;
    protected ?HolidayService $holidayService = null;

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->prepareCalendarData();
    }

    protected function getHolidayService(): HolidayService
    {
        if ($this->holidayService === null) {
            $this->holidayService = new HolidayService();
        }
        return $this->holidayService;
    }

    public function updatedBulan()
    {
        $this->prepareCalendarData();
    }

    public function updatedTahun()
    {
        $this->prepareCalendarData();
    }

    private function prepareCalendarData(): void
    {
        $user = Auth::user();
        $this->namaBulan = Carbon::create(null, $this->bulan)->translatedFormat('F');
        $startDate = Carbon::createFromDate($this->tahun, $this->bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = $this->getHolidayService()->getHolidays($this->tahun);

        $kehadiranBulanIni = $user->kehadiran()
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

        $this->weeks = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        $summaryCounts = array_fill_keys(['hadir', 'terlambat', 'cuti', 'sakit', 'izin', 'kompensasi', 'absen', 'libur'], 0);

        while ($currentDate->lte($endDate) || $currentDate->dayOfWeek != Carbon::SUNDAY) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                if ($currentDate->month != $this->bulan) {
                    $week[] = null;
                } else {
                    $dateKey = $currentDate->format('Y-m-d');
                    $isSunday = $currentDate->isSunday();
                    $isHoliday = isset($holidays[$dateKey]);
                    $holidayName = $holidays[$dateKey] ?? null;

                    $dayData = ['date' => $currentDate->day, 'status' => 'L', 'status_full' => 'Libur', 'holiday_name' => $holidayName];

                    if (isset($kehadiranBulanIni[$dateKey])) {
                        $k = $kehadiranBulanIni[$dateKey];
                        $statusMap = ['Tepat Waktu' => 'H', 'Terlambat' => 'T', 'Cuti' => 'C', 'Sakit' => 'S', 'Izin' => 'I', 'Kompensasi Libur' => 'K', 'Alfa' => 'A'];
                        $dayData['status'] = $statusMap[$k->status] ?? '??';
                        $dayData['status_full'] = $k->status;

                        if (in_array($k->status, ['Tepat Waktu', 'Terlambat'])) $summaryCounts['hadir']++;
                        if ($k->status === 'Terlambat') $summaryCounts['terlambat']++;
                        if ($k->status === 'Cuti') $summaryCounts['cuti']++;
                        if ($k->status === 'Sakit') $summaryCounts['sakit']++;
                        if ($k->status === 'Izin') $summaryCounts['izin']++;
                        if ($k->status === 'Kompensasi Libur') $summaryCounts['kompensasi']++;
                    } elseif ($isHoliday) {
                        $dayData['status'] = 'L';
                        $dayData['status_full'] = $holidayName;
                        $summaryCounts['libur']++;
                    } elseif (!$isSunday) {
                        $dayData['status'] = 'A';
                        $dayData['status_full'] = 'Absen';
                        $summaryCounts['absen']++;
                    } else {
                        $summaryCounts['libur']++;
                    }
                    $week[] = $dayData;
                }
                $currentDate->addDay();
            }
            $this->weeks[] = $week;
            if ($currentDate->month != $this->bulan && $currentDate > $endDate) break;
        }

        $this->summary = $summaryCounts;

        $workingDays = $startDate->diffInDaysFiltered(fn (Carbon $date) => !$date->isSunday() && !isset($holidays[$date->format('Y-m-d')]), $endDate->copy()->endOfDay());

        $this->statistics = [
            'working_days' => $workingDays,
            'attendance_rate' => $workingDays > 0 ? round(($summaryCounts['hadir'] / $workingDays) * 100) : 0,
            'leave_quota_used' => method_exists($user, 'getUsedLeaveQuotaInMonth') ? $user->getUsedLeaveQuotaInMonth($this->tahun, $this->bulan) : 0,
            'leave_quota_total' => $user->monthly_leave_quota ?? 0,
            'leave_quota_remaining' => method_exists($user, 'getRemainingLeaveQuotaInMonth') ? $user->getRemainingLeaveQuotaInMonth($this->tahun, $this->bulan) : 0,
        ];

        $this->compensation_stats = [
            'available_days' => method_exists($user, 'getTotalAvailableCompensationDays') ? $user->getTotalAvailableCompensationDays() : 0,
            'used_this_month' => method_exists($user, 'compensations') ? $user->compensations()->where('status', 'used')->whereMonth('compensation_date', $this->bulan)->whereYear('compensation_date', $this->tahun)->count() : 0,
            'expiring_soon' => method_exists($user, 'getExpiringCompensations') ? $user->getExpiringCompensations(30)->count() : 0,
        ];
    }
}