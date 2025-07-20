<?php

namespace App\Filament\Hrd\Resources\KehadiranResource\Pages;

use App\Filament\Hrd\Resources\KehadiranResource;
use App\Models\Kehadiran;
use App\Models\User;
use App\Models\Compensation;
use App\Services\HolidayService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ViewKehadiran extends ViewRecord
{
    protected static string $resource = KehadiranResource::class;
    protected static string $view = 'filament.hrd.pages.view-kehadiran';

    // Properti untuk filter dan data
    public $bulan;
    public $tahun;
    public $weeks = [];
    public $summary = [];
    public $statistics = [];
    public $compensation_stats = [];
    public $leave_requests;
    public $compensations;
    public $namaBulan;
    public ?string $selectedDate = null;

    // Properti service dibuat nullable untuk diinisialisasi secara "lazy"
    protected ?HolidayService $holidayService = null;

    /**
     * Dijalankan saat komponen pertama kali dimuat.
     */
    public function mount($record): void
    {
        parent::mount($record);
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->prepareCalendarData();
    }

    /**
     * Getter untuk memastikan HolidayService selalu terinisialisasi.
     * Ini adalah kunci untuk mencegah error inisialisasi.
     */
    protected function getHolidayService(): HolidayService
    {
        if ($this->holidayService === null) {
            $this->holidayService = new HolidayService();
        }
        return $this->holidayService;
    }

    /**
     * Mendefinisikan action modal untuk mengelola kehadiran.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageKehadiran')
                ->label('Kelola Kehadiran')
                ->modalHeading(fn () => 'Kelola Kehadiran untuk ' . Carbon::parse($this->selectedDate)->translatedFormat('d F Y'))
                ->modalWidth('2xl')
                ->form(function () {
                    $kehadiran = Kehadiran::where('user_id', $this->record->id)
                        ->where('tanggal', $this->selectedDate)
                        ->first();

                    return [
                        Select::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'Tepat Waktu' => '✅ Tepat Waktu',
                                'Terlambat' => '⏰ Terlambat',
                                'Sakit' => '🤒 Sakit',
                                'Izin' => '📝 Izin',
                                'Alfa' => '❌ Alfa',
                                'Kompensasi Libur' => '🔄 Kompensasi Libur',
                            ])
                            ->required()->reactive()->default($kehadiran?->status),
                        TimePicker::make('jam_masuk')->label('Jam Masuk')->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('status'), ['Tepat Waktu', 'Terlambat']))
                            ->default($kehadiran?->jam_masuk),
                        TimePicker::make('jam_pulang')->label('Jam Pulang')->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('status'), ['Tepat Waktu', 'Terlambat']))
                            ->default($kehadiran?->jam_pulang),
                        Select::make('compensation_id')->label('Pilih Kompensasi')
                            ->visible(fn (Get $get) => $get('status') === 'Kompensasi Libur')
                            ->options($this->record->getAvailableCompensations()->mapWithKeys(fn ($comp) => [$comp->id => "Dari kerja {$comp->work_date->format('d M')} (Exp: {$comp->expires_at->format('d M')})"]))
                            ->helperText('Pilih kompensasi dari kerja libur yang akan digunakan.')
                            ->required(fn (Get $get) => $get('status') === 'Kompensasi Libur')
                            ->default($kehadiran?->compensation_id),
                        Textarea::make('notes')->label('Keterangan/Catatan')->rows(3)
                            ->placeholder('Tambahkan catatan jika perlu (misal: alasan izin/sakit)')->default($kehadiran?->notes),
                    ];
                })
                ->action(function (array $data) {
                    try {
                        if ($data['status'] === 'Kompensasi Libur') {
                            $compensation = Compensation::find($data['compensation_id']);
                            if (!$compensation || !$compensation->canBeUsed(Carbon::parse($this->selectedDate))) {
                                Notification::make()->title('Gagal')->body('Kompensasi tidak valid atau sudah digunakan.')->danger()->send();
                                return;
                            }
                            Kehadiran::where('user_id', $this->record->id)->where('tanggal', $this->selectedDate)->delete();
                            $compensation->use(Carbon::parse($this->selectedDate), $data['notes']);
                        } else {
                            Kehadiran::updateOrCreate(
                                ['user_id' => $this->record->id, 'tanggal' => $this->selectedDate],
                                [
                                    'status' => $data['status'],
                                    'jam_masuk' => $data['jam_masuk'] ?? null,
                                    'jam_pulang' => $data['jam_pulang'] ?? null,
                                    'notes' => $data['notes'],
                                    'metode_absen' => 'manual_hrd',
                                    'compensation_id' => null, 'leave_request_id' => null,
                                ]
                            );
                        }
                        Notification::make()->title('Berhasil Disimpan')->body('Data kehadiran telah diperbarui.')->success()->send();
                        $this->prepareCalendarData();
                    } catch (\Exception $e) {
                        Notification::make()->title('Terjadi Kesalahan')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    /**
     * Dipanggil dari view saat tanggal di kalender diklik.
     */
    public function openModalForDate(string $date)
    {
        $this->selectedDate = $date;
        $this->mountAction('manageKehadiran');
    }

    /**
     * Livewire hook, dijalankan saat filter bulan diubah.
     */
    public function updatedBulan()
    {
        $this->prepareCalendarData();
    }

    /**
     * Livewire hook, dijalankan saat filter tahun diubah.
     */
    public function updatedTahun()
    {
        $this->prepareCalendarData();
    }

    /**
     * Metode utama untuk menyiapkan semua data yang akan ditampilkan di view.
     */
    private function prepareCalendarData(): void
    {
        $this->namaBulan = Carbon::create(null, $this->bulan)->translatedFormat('F');
        $startDate = Carbon::createFromDate($this->tahun, $this->bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Selalu panggil service melalui metode getter untuk memastikan inisialisasi.
        $holidays = $this->getHolidayService()->getHolidays($this->tahun);

        $kehadiranBulanIni = $this->record->kehadiran()
            ->with(['leaveRequest', 'compensation'])
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

        $this->weeks = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        $summaryCounts = array_fill_keys(['hadir', 'terlambat', 'cuti', 'sakit', 'izin', 'kompensasi', 'absen', 'libur'], 0);

        // Loop untuk membangun data kalender per minggu.
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

                    $dayData = ['date' => $currentDate->day, 'full_date' => $dateKey, 'status' => 'L', 'status_full' => 'Libur', 'jam_masuk' => '-', 'jam_pulang' => '-', 'leave_type' => null, 'compensation_info' => null, 'holiday_name' => $holidayName];

                    if (isset($kehadiranBulanIni[$dateKey])) {
                        $k = $kehadiranBulanIni[$dateKey];
                        $statusMap = ['Tepat Waktu' => 'H', 'Terlambat' => 'T', 'Cuti' => 'C', 'Sakit' => 'S', 'Izin' => 'I', 'Kompensasi Libur' => 'K', 'Alfa' => 'A'];
                        $dayData['status'] = $statusMap[$k->status] ?? '??';
                        $dayData['status_full'] = $k->status;
                        $dayData['jam_masuk'] = $k->jam_masuk ? Carbon::parse($k->jam_masuk)->format('H:i') : '-';
                        $dayData['jam_pulang'] = $k->jam_pulang ? Carbon::parse($k->jam_pulang)->format('H:i') : '-';
                        $dayData['leave_type'] = $k->leaveRequest?->leave_type_name;
                        $dayData['compensation_info'] = $k->compensation ? 'Kompensasi' : null;

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
            'leave_quota_used' => $this->record->getUsedLeaveQuotaInMonth($this->tahun, $this->bulan),
            'leave_quota_total' => $this->record->monthly_leave_quota,
            'leave_quota_remaining' => $this->record->getRemainingLeaveQuotaInMonth($this->tahun, $this->bulan),
        ];

        $this->compensation_stats = [
            'available_days' => $this->record->getTotalAvailableCompensationDays(),
            'used_this_month' => $this->record->compensations()->where('status', 'used')->whereMonth('compensation_date', $this->bulan)->whereYear('compensation_date', $this->tahun)->count(),
            'expiring_soon' => $this->record->getExpiringCompensations(30)->count(),
        ];
    }
}