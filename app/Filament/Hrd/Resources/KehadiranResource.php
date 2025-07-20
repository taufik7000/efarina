<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KehadiranResource\Pages;
use App\Models\User;
use App\Models\Kehadiran;
use App\Models\Compensation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class KehadiranResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Absensi';
    protected static ?string $navigationLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Absensi';
    protected static ?int $navigationSort = 1;


public static function getPluralModelLabel(): string
{
    Carbon::setLocale('id');
    $tanggalHariIni = Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y');

    return "Absensi Karyawan ({$tanggalHariIni})";
}


    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(
                User::query()->with(['kehadiran' => function ($query) {
                    $query->whereDate('tanggal', today('Asia/Jakarta'))
                          ->with(['leaveRequest', 'compensation']);
                }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->jam_masuk)
                    ->time('H:i:s')
                    ->placeholder('--')
                    ->color(fn (User $record): string =>
                        ($kehadiran = $record->kehadiran->first()) && $kehadiran->jam_masuk
                            ? ($kehadiran->status === 'Terlambat' ? 'warning' : 'success')
                            : 'gray'
                    ),
                
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->jam_pulang)
                    ->time('H:i:s')
                    ->placeholder('--'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Kehadiran')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->status ?? 'Belum Absen')
                    ->colors([
                        'success' => 'Tepat Waktu',
                        'warning' => ['Terlambat', 'Sakit', 'Izin'],
                        'danger' => 'Alfa',
                        'info' => ['Cuti', 'Kompensasi Libur'],
                        'gray' => 'Belum Absen',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'Tepat Waktu' => 'Tepat Waktu',
                        'Terlambat' => 'Terlambat',
                        'Alfa' => 'Alfa',
                        'Cuti' => 'Cuti',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Kompensasi Libur' => 'Kompensasi',
                        default => 'Belum Absen'
                    }),

                Tables\Columns\TextColumn::make('leave_info')
                    ->label('Keterangan')
                    ->state(function (User $record): ?string {
                        $kehadiran = $record->kehadiran->first();
                        if (!$kehadiran) return null;
                        
                        // Kompensasi libur
                        if ($kehadiran->status === 'Kompensasi Libur' && $kehadiran->compensation) {
                            return 'Kompensasi dari ' . 
                                   $kehadiran->compensation->work_date->format('d M Y') . 
                                   ': ' . substr($kehadiran->compensation->work_reason, 0, 30) . 
                                   (strlen($kehadiran->compensation->work_reason) > 30 ? '...' : '');
                        }
                        
                        // Cuti reguler
                        if ($kehadiran->status === 'Cuti' && $kehadiran->leaveRequest) {
                            return $kehadiran->leaveRequest->leave_type_name . 
                                   ': ' . substr($kehadiran->leaveRequest->reason, 0, 30) . 
                                   (strlen($kehadiran->leaveRequest->reason) > 30 ? '...' : '');
                        }
                        
                        // Notes manual
                        if ($kehadiran->notes) {
                            return substr($kehadiran->notes, 0, 40) . 
                                   (strlen($kehadiran->notes) > 40 ? '...' : '');
                        }
                        
                        return null;
                    })
                    ->placeholder('--')
                    ->tooltip(function (User $record): ?string {
                        $kehadiran = $record->kehadiran->first();
                        if (!$kehadiran) return null;
                        
                        if ($kehadiran->status === 'Kompensasi Libur' && $kehadiran->compensation) {
                            return "Kompensasi dari kerja hari libur: {$kehadiran->compensation->work_reason}";
                        }
                        
                        if ($kehadiran->status === 'Cuti' && $kehadiran->leaveRequest) {
                            return "Cuti: {$kehadiran->leaveRequest->reason}";
                        }
                        
                        return $kehadiran->notes;
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('monthly_quota_info')
                    ->label('Kuota Cuti')
                    ->state(function (User $record): string {
                        $currentYear = now()->year;
                        $currentMonth = now()->month;
                        $used = method_exists($record, 'getUsedLeaveQuotaInMonth') ? 
                            $record->getUsedLeaveQuotaInMonth($currentYear, $currentMonth) : 0;
                        $remaining = method_exists($record, 'getRemainingLeaveQuotaInMonth') ? 
                            $record->getRemainingLeaveQuotaInMonth($currentYear, $currentMonth) : 0;
                        $total = $record->monthly_leave_quota ?? 2;
                        
                        return "{$used}/{$total} (sisa: {$remaining})";
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $remaining = method_exists($record, 'getRemainingLeaveQuotaInMonth') ? 
                            $record->getRemainingLeaveQuotaInMonth(now()->year, now()->month) : 0;
                        return match(true) {
                            $remaining <= 0 => 'danger',
                            $remaining <= 1 => 'warning',
                            default => 'success'
                        };
                    }),

                Tables\Columns\TextColumn::make('compensation_info')
                    ->label('Kompensasi')
                    ->state(function (User $record): string {
                        if (!method_exists($record, 'getTotalAvailableCompensationDays')) {
                            return 'N/A';
                        }
                        
                        $available = $record->getTotalAvailableCompensationDays();
                        $expiring = method_exists($record, 'getExpiringCompensations') ? 
                            $record->getExpiringCompensations(7)->count() : 0;
                        
                        if ($available === 0) return 'Tidak ada';
                        
                        $text = "{$available} hari";
                        if ($expiring > 0) {
                            $text .= " ({$expiring} exp)";
                        }
                        
                        return $text;
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        if (!method_exists($record, 'getTotalAvailableCompensationDays')) {
                            return 'gray';
                        }
                        
                        $available = $record->getTotalAvailableCompensationDays();
                        $expiring = method_exists($record, 'getExpiringCompensations') ? 
                            $record->getExpiringCompensations(7)->count() : 0;
                        
                        return match(true) {
                            $available === 0 => 'gray',
                            $expiring > 0 => 'warning',
                            $available >= 3 => 'success',
                            default => 'info'
                        };
                    })
                    ->tooltip(function (User $record): ?string {
                        if (!method_exists($record, 'getAvailableCompensations')) {
                            return null;
                        }
                        
                        $compensations = $record->getAvailableCompensations();
                        if ($compensations->isEmpty()) return null;
                        
                        return $compensations->map(function ($comp) {
                            return "• Kerja {$comp->work_date->format('d M')} - Exp: {$comp->expires_at->format('d M')}";
                        })->join("\n");
                    }),

                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->placeholder('Tidak ada jabatan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'Tepat Waktu' => 'Tepat Waktu',
                        'Terlambat' => 'Terlambat',
                        'Alfa' => 'Alfa',
                        'Cuti' => 'Cuti',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Kompensasi Libur' => 'Kompensasi Libur',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->whereHas('kehadiran', function ($query) use ($data) {
                            $query->whereDate('tanggal', today('Asia/Jakarta'))
                                  ->where('status', $data['value']);
                        });
                    }),

                Tables\Filters\Filter::make('on_leave_today')
                    ->label('Sedang Cuti/Kompensasi')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('kehadiran', function ($query) {
                            $query->whereDate('tanggal', today('Asia/Jakarta'))
                                  ->whereIn('status', ['Cuti', 'Kompensasi Libur']);
                        });
                    }),

                Tables\Filters\Filter::make('not_present')
                    ->label('Belum Hadir')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query->whereDoesntHave('kehadiran', function ($query) {
                            $query->whereDate('tanggal', today('Asia/Jakarta'));
                        });
                    }),

                Tables\Filters\Filter::make('has_compensation')
                    ->label('Punya Kompensasi')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('compensations', function ($query) {
                            $query->where('status', 'earned')
                                  ->where('expires_at', '>', now());
                        });
                    }),
            ])
            ->actions([
                // Action untuk tandai izin/sakit/kompensasi
          Tables\Actions\Action::make('mark_manual_leave')
                ->label('Update Status')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->visible(function (User $record) {
                    // Hanya tampilkan jika belum ada record kehadiran hari ini
                    return !$record->kehadiran()
                        ->whereDate('tanggal', today('Asia/Jakarta'))
                        ->exists();
                })
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'Kompensasi Libur' => '🔄 Kompensasi Libur',
                            'Sakit' => '🤒 Sakit',
                            'Izin' => '📝 Izin',
                            'Alfa' => '❌ Alfa (Tanpa Keterangan)',
                        ])
                        ->required()
                        ->reactive(),
                    
                    Forms\Components\Select::make('compensation_id')
                        ->label('Pilih Kompensasi yang Tersedia')
                        ->visible(fn (Forms\Get $get) => $get('status') === 'Kompensasi Libur')
                        ->options(function (User $record) {
                            if (!method_exists($record, 'getAvailableCompensations')) {
                                return [];
                            }
                            
                            // Ambil kompensasi yang tersedia dan format untuk select options
                            return $record->getAvailableCompensations()
                                ->mapWithKeys(function ($comp) {
                                    return [
                                        $comp->id => "Dari kerja tgl {$comp->work_date->format('d M Y')} (Exp: {$comp->expires_at->format('d M Y')})"
                                    ];
                                });
                        })
                        ->placeholder('Pilih kompensasi yang akan digunakan')
                        ->helperText('Hanya kompensasi yang valid dan belum kadaluarsa yang akan tampil.')
                        ->required(fn (Forms\Get $get) => $get('status') === 'Kompensasi Libur'),
                    
                    Forms\Components\Textarea::make('notes')
                        ->label('Keterangan')
                        ->placeholder('Masukkan keterangan detail (opsional)')
                        ->rows(3),

                    Forms\Components\FileUpload::make('attachment')
                        ->label('Lampiran')
                        ->directory('manual-attendance')
                        ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png'])
                        ->maxSize(2048)
                        ->helperText('Upload surat dokter atau bukti pendukung (opsional)')
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['Sakit', 'Izin'])),
                ])
                ->action(function (User $record, array $data) {
                    $today = today('Asia/Jakarta');

                    // 1. Cek sekali lagi jika ada record kehadiran yang dibuat saat form terbuka
                    $existingAttendance = Kehadiran::where('user_id', $record->id)
                        ->whereDate('tanggal', $today)
                        ->first();

                    if ($existingAttendance) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Karyawan ini sudah memiliki catatan kehadiran hari ini.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // 2. Logika khusus untuk Kompensasi Libur
                    if ($data['status'] === 'Kompensasi Libur') {
                        $compensation = Compensation::find($data['compensation_id']);
                        
                        // Validasi kompensasi
                        if (!$compensation || !$compensation->canBeUsed()) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Kompensasi tidak valid atau sudah tidak bisa digunakan.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Panggil method `use()` pada model Compensation
                        // Method ini akan menangani update status kompensasi DAN pembuatan record kehadiran
                        if ($compensation->use($today, $data['notes'])) {
                            Notification::make()
                                ->title('Kompensasi Berhasil Digunakan')
                                ->body("Status kehadiran {$record->name} telah diperbarui menjadi 'Kompensasi Libur'.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Terjadi kesalahan saat mencoba menggunakan kompensasi.')
                                ->danger()
                                ->send();
                        }
                        return; // Selesai setelah menangani kompensasi
                    }

                    // 3. Logika untuk status lain (Sakit, Izin, Alfa)
                    Kehadiran::create([
                        'user_id' => $record->id,
                        'tanggal' => $today,
                        'status' => $data['status'],
                        'notes' => $data['notes'],
                        'metode_absen' => 'manual_hrd',
                    ]);

                    Notification::make()
                        ->title('Berhasil')
                        ->body("Status kehadiran {$record->name} telah diperbarui menjadi {$data['status']}.")
                        ->success()
                        ->send();
                    }),

                // Action buat kompensasi dari kerja hari libur
                Tables\Actions\Action::make('create_compensation')
                    ->label('Buat Kompensasi')
                    ->icon('heroicon-o-plus-circle')
                    ->color('info')
                    ->visible(function (User $record) {
                        // Tampil jika user kerja di hari libur (Minggu) hari ini
                        return today()->dayOfWeek === Carbon::SUNDAY && 
                               $record->kehadiran()
                                   ->whereDate('tanggal', today('Asia/Jakarta'))
                                   ->whereIn('status', ['Tepat Waktu', 'Terlambat'])
                                   ->exists();
                    })
                    ->form([
                        Forms\Components\Placeholder::make('info')
                            ->label('Informasi')
                            ->content(function (User $record) {
                                $kehadiran = $record->kehadiran()
                                    ->whereDate('tanggal', today('Asia/Jakarta'))
                                    ->first();
                                
                                if (!$kehadiran) return 'Tidak ada data kehadiran';
                                
                                return "Karyawan masuk kerja hari ini (Minggu) pada jam {$kehadiran->jam_masuk}. Buat kompensasi libur untuk hari kerja lainnya.";
                            }),
                            
                        Forms\Components\TimePicker::make('work_start_time')
                            ->label('Jam Mulai Kerja')
                            ->default(function (User $record) {
                                $kehadiran = $record->kehadiran()->whereDate('tanggal', today())->first();
                                return $kehadiran?->jam_masuk;
                            })
                            ->required(),

                        Forms\Components\TimePicker::make('work_end_time')
                            ->label('Jam Selesai Kerja')
                            ->default(function (User $record) {
                                $kehadiran = $record->kehadiran()->whereDate('tanggal', today())->first();
                                return $kehadiran?->jam_pulang ?? now()->format('H:i');
                            })
                            ->required(),

                        Forms\Components\Textarea::make('work_reason')
                            ->label('Alasan Kerja di Hari Libur')
                            ->required()
                            ->placeholder('Contoh: Project urgent, maintenance sistem, acara khusus, dll')
                            ->rows(3),

                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Kompensasi Kadaluarsa')
                            ->default(today()->addDays(90))
                            ->minDate(today()->addDays(30))
                            ->maxDate(today()->addDays(180))
                            ->helperText('Kompensasi harus digunakan sebelum tanggal ini')
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        if (!class_exists('App\Models\Compensation')) {
                            Notification::make()
                                ->title('Error')
                                ->body('Model Compensation belum tersedia.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!method_exists($record, 'createCompensationFromHolidayWork')) {
                            Notification::make()
                                ->title('Error')
                                ->body('Method createCompensationFromHolidayWork belum tersedia di User model.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $startTime = Carbon::createFromFormat('H:i', $data['work_start_time']);
                        $endTime = Carbon::createFromFormat('H:i', $data['work_end_time']);
                        
                        if ($endTime->lte($startTime)) {
                            Notification::make()
                                ->title('Error')
                                ->body('Jam selesai harus lebih besar dari jam mulai.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $compensation = $record->createCompensationFromHolidayWork(
                            today(),
                            $startTime,
                            $endTime,
                            $data['work_reason'],
                            auth()->id()
                        );
                        
                        Notification::make()
                            ->title('Kompensasi Dibuat')
                            ->body("Kompensasi libur berhasil dibuat. Berlaku hingga {$compensation->expires_at->format('d M Y')}.")
                            ->success()
                            ->send();
                    }),

                // Action edit kehadiran
                Tables\Actions\Action::make('edit_attendance')
                    ->label('Edit Kehadiran')
                    ->icon('heroicon-o-pencil')
                    ->color('info')
                    ->visible(function (User $record) {
                        return $record->kehadiran()
                            ->whereDate('tanggal', today('Asia/Jakarta'))
                            ->exists();
                    })
                    ->form(function (User $record) {
                        $kehadiran = $record->kehadiran()
                            ->whereDate('tanggal', today('Asia/Jakarta'))
                            ->first();

                        return [
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Tepat Waktu' => '✅ Tepat Waktu',
                                    'Terlambat' => '⏰ Terlambat',
                                    'Sakit' => '🤒 Sakit',
                                    'Izin' => '📝 Izin',
                                    'Alfa' => '❌ Alfa',
                                    'Kompensasi Libur' => '🔄 Kompensasi Libur',
                                ])
                                ->required()
                                ->default($kehadiran?->status),
                            
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Tambahkan catatan koreksi')
                                ->rows(3)
                                ->default($kehadiran?->notes),

                            Forms\Components\Placeholder::make('info')
                                ->label('Info Kehadiran Saat Ini')
                                ->content(function () use ($kehadiran) {
                                    if (!$kehadiran) return 'Tidak ada data kehadiran';
                                    
                                    $info = "Status: {$kehadiran->status}";
                                    if ($kehadiran->jam_masuk) {
                                        $info .= " | Masuk: {$kehadiran->jam_masuk}";
                                    }
                                    if ($kehadiran->jam_pulang) {
                                        $info .= " | Pulang: {$kehadiran->jam_pulang}";
                                    }
                                    if ($kehadiran->metode_absen) {
                                        $info .= " | Metode: {$kehadiran->metode_absen}";
                                    }
                                    
                                    return $info;
                                }),
                        ];
                    })
                    ->action(function (User $record, array $data) {
                        $kehadiran = $record->kehadiran()
                            ->whereDate('tanggal', today('Asia/Jakarta'))
                            ->first();

                        if ($kehadiran) {
                            $kehadiran->update([
                                'status' => $data['status'],
                                'notes' => $data['notes'],
                            ]);

                            Notification::make()
                                ->title('Kehadiran Diperbarui')
                                ->body("Status kehadiran {$record->name} telah diperbarui.")
                                ->success()
                                ->send();
                        }
                    }),

                // Action lihat detail
                Tables\Actions\Action::make('view_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (User $record) => "Detail Kehadiran - {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->infolist(function (Infolist $infolist) {
                        return $infolist
                            ->record($infolist->getRecord()->kehadiran->first())
                            ->schema([
                                Components\Section::make('Status & Waktu')
                                    ->schema([
                                        Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (?string $state): string => match ($state) {
                                                'Tepat Waktu' => 'success',
                                                'Terlambat' => 'warning',
                                                'Alfa' => 'danger',
                                                'Cuti' => 'info',
                                                'Sakit' => 'warning',
                                                'Izin' => 'gray',
                                                'Kompensasi Libur' => 'info',
                                                default => 'gray',
                                            }),
                                        
                                        Components\TextEntry::make('jam_masuk')
                                            ->label('Jam Masuk')
                                            ->time('H:i:s')
                                            ->placeholder('Belum absen masuk'),
                                        
                                        Components\TextEntry::make('jam_pulang')
                                            ->label('Jam Pulang')
                                            ->time('H:i:s')
                                            ->placeholder('Belum absen pulang'),

                                        Components\TextEntry::make('metode_absen')
                                            ->label('Metode Absen')
                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                'qrcode' => 'QR Code',
                                                'manual_hrd' => 'Manual HRD',
                                                'system_generated' => 'System (Cuti/Kompensasi)',
                                                default => ucfirst($state ?? 'Unknown')
                                            })
                                            ->badge(),
                                    ])
                                    ->columns(2),

                                Components\Section::make('Informasi Detail')
                                    ->schema([
                                        Components\TextEntry::make('notes')
                                            ->label('Catatan')
                                            ->placeholder('Tidak ada catatan'),

                                        Components\TextEntry::make('compensation.work_date')
                                            ->label('Kompensasi dari Tanggal')
                                            ->date('d M Y')
                                            ->visible(fn ($record) => $record?->status === 'Kompensasi Libur'),

                                        Components\TextEntry::make('compensation.work_reason')
                                            ->label('Alasan Kerja Libur')
                                            ->visible(fn ($record) => $record?->status === 'Kompensasi Libur'),

                                        Components\TextEntry::make('leaveRequest.leave_type_name')
                                            ->label('Jenis Cuti')
                                            ->visible(fn ($record) => $record?->status === 'Cuti'),

                                        Components\TextEntry::make('leaveRequest.reason')
                                            ->label('Alasan Cuti')
                                            ->visible(fn ($record) => $record?->status === 'Cuti'),
                                    ])
                                    ->columns(1),
                            ]);
                    })
                    ->visible(fn (User $record) => $record->kehadiran->isNotEmpty()),

                Tables\Actions\Action::make('view_monthly')
                    ->label('Laporan Bulanan')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn (User $record): string => static::getUrl('view', ['record' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_all_present')
                    ->label('Tandai Hadir Semua')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $processed = 0;
                        $skipped = 0;

                        foreach ($records as $user) {
                            $existingAttendance = Kehadiran::where('user_id', $user->id)
                                ->whereDate('tanggal', today('Asia/Jakarta'))
                                ->first();

                            if (!$existingAttendance) {
                                Kehadiran::create([
                                    'user_id' => $user->id,
                                    'tanggal' => today('Asia/Jakarta'),
                                    'jam_masuk' => now()->format('H:i:s'),
                                    'status' => 'Tepat Waktu',
                                    'metode_absen' => 'bulk_manual_hrd',
                                    'notes' => 'Ditandai hadir secara massal oleh HRD',
                                ]);
                                $processed++;
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title('Bulk Update Selesai')
                            ->body("Berhasil memproses {$processed} karyawan, {$skipped} dilewati (sudah ada record).")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('export_attendance')
                    ->label('Export Kehadiran')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function ($records) {
                        // Implementation for exporting attendance data
                        Notification::make()
                            ->title('Export Dimulai')
                            ->body('Data kehadiran sedang diproses untuk export.')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKehadirans::route('/'),
            'view' => Pages\ViewKehadiran::route('/{record}/view'),
        ];
    }
    
    public static function canCreate(): bool
    {
       return false;
    }

    // Helper method untuk mendapatkan summary stats
    public static function getNavigationBadge(): ?string
    {
        $absentCount = User::whereDoesntHave('kehadiran', function ($query) {
            $query->whereDate('tanggal', today('Asia/Jakarta'));
        })->count();

        return $absentCount > 0 ? (string) $absentCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $absentCount = User::whereDoesntHave('kehadiran', function ($query) {
            $query->whereDate('tanggal', today('Asia/Jakarta'));
        })->count();

        return $absentCount > 5 ? 'danger' : ($absentCount > 0 ? 'warning' : 'success');
    }
}