<?php

namespace App\Filament\Hrd\Resources\KehadiranResource\Pages;

use App\Filament\Hrd\Resources\KehadiranResource;
use Filament\Resources\Pages\Page;
use App\Models\User;
use App\Models\Kehadiran;
use App\Models\LeaveRequest;
use App\Models\Compensation;
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
        
        // Ambil data kehadiran dengan relasi compensation
        $dataKehadiran = Kehadiran::where('user_id', $this->record->id)
                                  ->whereYear('tanggal', $this->tahun)
                                  ->whereMonth('tanggal', $this->bulan)
                                  ->with(['leaveRequest', 'compensation']) // 🔥 TAMBAH RELASI COMPENSATION
                                  ->get()
                                  ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->day);

        // Ambil data leave requests untuk bulan ini
        $leaveRequests = LeaveRequest::where('user_id', $this->record->id)
                                    ->where('status', 'approved')
                                    ->where(function($query) use ($tanggalAwal) {
                                        $query->whereMonth('start_date', $this->bulan)
                                              ->whereYear('start_date', $this->tahun)
                                              ->orWhereMonth('end_date', $this->bulan)
                                              ->whereYear('end_date', $this->tahun);
                                    })
                                    ->get();

        // 🔥 TAMBAH DATA COMPENSATIONS UNTUK BULAN INI
        $compensations = Compensation::where('user_id', $this->record->id)
                                   ->whereYear('work_date', $this->tahun)
                                   ->whereMonth('work_date', $this->bulan)
                                   ->orderBy('work_date', 'desc')
                                   ->get();

        $summary = [
            'hadir' => 0, 
            'terlambat' => 0, 
            'absen' => 0, 
            'cuti' => 0, 
            'sakit' => 0, 
            'izin' => 0,
            'libur' => 0,
            'kompensasi' => 0  // 🔥 TAMBAH SUMMARY KOMPENSASI
        ];
        
        $days = [];

        // Tambahkan hari kosong di awal untuk alignment kalender
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
                
                // Tentukan status dan warna berdasarkan data kehadiran
                $statusCode = $this->getStatusCode($kehadiranHariIni->status);
                $summary[$this->getStatusKey($kehadiranHariIni->status)]++;
                
                $dayData = [
                    'date' => $i,
                    'status' => $statusCode,
                    'status_full' => $kehadiranHariIni->status,
                    'jam_masuk' => $kehadiranHariIni->jam_masuk ? 
                        Carbon::parse($kehadiranHariIni->jam_masuk)->format('H:i') : '-',
                    'jam_pulang' => $kehadiranHariIni->jam_pulang ? 
                        Carbon::parse($kehadiranHariIni->jam_pulang)->format('H:i') : '-',
                    'notes' => $kehadiranHariIni->notes,
                    'leave_type' => $kehadiranHariIni->leaveRequest?->leave_type_name ?? null,
                    'leave_reason' => $kehadiranHariIni->leaveRequest?->reason ?? null,
                    'metode_absen' => $kehadiranHariIni->metode_absen,
                    // 🔥 TAMBAH INFO COMPENSATION
                    'compensation_info' => $kehadiranHariIni->compensation ? [
                        'work_date' => $kehadiranHariIni->compensation->work_date->format('d M Y'),
                        'work_reason' => $kehadiranHariIni->compensation->work_reason,
                        'work_hours' => $kehadiranHariIni->compensation->work_hours
                    ] : null,
                ];
            } else {
                // Jika tidak ada data kehadiran
                if ($hari->isSunday()) {
                    $dayData = [
                        'date' => $i,
                        'status' => 'L',
                        'status_full' => 'Libur',
                        'jam_masuk' => 'Libur',
                        'jam_pulang' => '-',
                        'notes' => 'Hari Minggu',
                        'leave_type' => null,
                        'leave_reason' => null,
                        'metode_absen' => null,
                        'compensation_info' => null,
                    ];
                    $summary['libur']++;
                } else {
                    // Cek apakah hari ini seharusnya cuti berdasarkan leave request
                    $isOnLeave = $this->checkIfOnLeave($hari, $leaveRequests);
                    
                    if ($isOnLeave) {
                        $dayData = [
                            'date' => $i,
                            'status' => 'C',
                            'status_full' => 'Cuti',
                            'jam_masuk' => 'Cuti',
                            'jam_pulang' => '-',
                            'notes' => $isOnLeave['reason'],
                            'leave_type' => $isOnLeave['type'],
                            'leave_reason' => $isOnLeave['reason'],
                            'metode_absen' => 'system_generated',
                            'compensation_info' => null,
                        ];
                        $summary['cuti']++;
                    } else {
                        // Jika tanggal sudah lewat dan tidak ada data, dianggap absen
                        if ($hari->isPast() && !$hari->isToday()) {
                            $dayData = [
                                'date' => $i,
                                'status' => 'A',
                                'status_full' => 'Alfa',
                                'jam_masuk' => 'Absen',
                                'jam_pulang' => '-',
                                'notes' => 'Tidak ada catatan kehadiran',
                                'leave_type' => null,
                                'leave_reason' => null,
                                'metode_absen' => null,
                                'compensation_info' => null,
                            ];
                            $summary['absen']++;
                        } else {
                            $dayData = [
                                'date' => $i,
                                'status' => '-',
                                'status_full' => 'Belum Absen',
                                'jam_masuk' => '-',
                                'jam_pulang' => '-',
                                'notes' => null,
                                'leave_type' => null,
                                'leave_reason' => null,
                                'metode_absen' => null,
                                'compensation_info' => null,
                            ];
                        }
                    }
                }
            }
            $days[] = $dayData;
        }

        // Hitung statistik tambahan
        $workingDays = $this->record->getWorkingDaysInMonth($this->tahun, $this->bulan);
        $attendanceRate = $workingDays > 0 ? 
            round((($summary['hadir'] + $summary['terlambat']) / $workingDays) * 100, 1) : 0;
        
        $leaveQuotaUsed = $this->record->getUsedLeaveQuotaInMonth($this->tahun, $this->bulan);
        $leaveQuotaRemaining = $this->record->getRemainingLeaveQuotaInMonth($this->tahun, $this->bulan);

        // 🔥 TAMBAH COMPENSATION STATS
        $compensation_stats = [
            'available_days' => $this->record->getTotalAvailableCompensationDays(),
            'used_this_month' => $summary['kompensasi'],
            'total_earned_this_month' => $compensations->where('status', 'earned')->count(),
            'expiring_soon' => $this->record->getExpiringCompensations(30)->count(),
        ];

        return [
            'weeks' => array_chunk($days, 7),
            'summary' => $summary,
            'statistics' => [
                'working_days' => $workingDays,
                'attendance_rate' => $attendanceRate,
                'leave_quota_used' => $leaveQuotaUsed,
                'leave_quota_remaining' => $leaveQuotaRemaining,
                'leave_quota_total' => $this->record->monthly_leave_quota,
            ],
            'namaBulan' => $tanggalAwal->translatedFormat('F'),
            'leave_requests' => $leaveRequests,
            'compensations' => $compensations, // 🔥 TAMBAH DATA COMPENSATIONS
            'compensation_stats' => $compensation_stats, // 🔥 TAMBAH STATS COMPENSATIONS
        ];
    }

    private function getStatusCode($status): string
    {
        return match($status) {
            'Tepat Waktu' => 'H',
            'Terlambat' => 'T',
            'Alfa' => 'A', 
            'Cuti' => 'C',
            'Sakit' => 'S',
            'Izin' => 'I',
            'Kompensasi Libur' => 'K', // 🔥 TAMBAH STATUS KOMPENSASI
            default => '-'
        };
    }

    private function getStatusKey($status): string
    {
        return match($status) {
            'Tepat Waktu' => 'hadir',
            'Terlambat' => 'terlambat',
            'Alfa' => 'absen',
            'Cuti' => 'cuti',
            'Sakit' => 'sakit',
            'Izin' => 'izin',
            'Kompensasi Libur' => 'kompensasi', // 🔥 TAMBAH MAPPING STATUS
            default => 'absen'
        };
    }

    private function checkIfOnLeave($date, $leaveRequests)
    {
        foreach ($leaveRequests as $leave) {
            if ($date->between($leave->start_date, $leave->end_date)) {
                return [
                    'type' => $leave->leave_type_name,
                    'reason' => $leave->reason
                ];
            }
        }
        return false;
    }
}
