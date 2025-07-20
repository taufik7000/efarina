<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KehadiranResource\Pages;
use App\Models\User;
use App\Models\Kehadiran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class KehadiranResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Absensi Karyawan';
    protected static ?string $navigationLabel = 'Absensi Hari Ini';
    protected static ?string $pluralModelLabel = 'Absensi Hari Ini';
    protected static ?int $navigationSort = 1;

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
                    $query->whereDate('tanggal', today('Asia/Jakarta'));
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
                    ->color(function (User $record): string {
                        $kehadiran = $record->kehadiran->first();
                        if (!$kehadiran || !$kehadiran->jam_masuk) return 'gray';
                        return $kehadiran->status === 'Terlambat' ? 'warning' : 'success';
                    }),
                
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
                        'info' => 'Cuti',
                        'gray' => 'Belum Absen',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Belum Absen'),

                // KOLOM BARU: Info Cuti/Izin dengan detail
                Tables\Columns\TextColumn::make('leave_info')
                    ->label('Keterangan')
                    ->state(function (User $record): ?string {
                        $kehadiran = $record->kehadiran->first();
                        if (!$kehadiran) return null;
                        
                        // Jika status cuti, tampilkan info dari leave request
                        if ($kehadiran->status === 'Cuti' && $kehadiran->leaveRequest) {
                            return $kehadiran->leaveRequest->leave_type_name . 
                                   ': ' . substr($kehadiran->leaveRequest->reason, 0, 30) . 
                                   (strlen($kehadiran->leaveRequest->reason) > 30 ? '...' : '');
                        }
                        
                        // Jika ada notes manual
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
                        
                        if ($kehadiran->status === 'Cuti' && $kehadiran->leaveRequest) {
                            return "Cuti {$kehadiran->leaveRequest->leave_type_name}: {$kehadiran->leaveRequest->reason}";
                        }
                        
                        return $kehadiran->notes;
                    })
                    ->wrap(),

                // KOLOM BARU: Kuota Cuti Bulanan
                Tables\Columns\TextColumn::make('monthly_quota_info')
                    ->label('Kuota Cuti Bulan Ini')
                    ->state(function (User $record): string {
                        $currentYear = now()->year;
                        $currentMonth = now()->month;
                        $used = $record->getUsedLeaveQuotaInMonth($currentYear, $currentMonth);
                        $remaining = $record->getRemainingLeaveQuotaInMonth($currentYear, $currentMonth);
                        $total = $record->monthly_leave_quota;
                        
                        return "{$used}/{$total} (sisa: {$remaining})";
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $remaining = $record->getRemainingLeaveQuotaInMonth(now()->year, now()->month);
                        return match(true) {
                            $remaining <= 0 => 'danger',
                            $remaining <= 1 => 'warning',
                            default => 'success'
                        };
                    })
                    ->tooltip(function (User $record): string {
                        $currentYear = now()->year;
                        $currentMonth = now()->month;
                        $used = $record->getUsedLeaveQuotaInMonth($currentYear, $currentMonth);
                        $total = $record->monthly_leave_quota;
                        $percentage = $total > 0 ? round(($used / $total) * 100, 1) : 0;
                        return "Penggunaan kuota: {$percentage}%";
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
                    ->label('Sedang Cuti Hari Ini')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('kehadiran', function ($query) {
                            $query->whereDate('tanggal', today('Asia/Jakarta'))
                                  ->where('status', 'Cuti');
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

                Tables\Filters\Filter::make('quota_low')
                    ->label('Kuota Cuti Rendah (≤1 hari)')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        $currentYear = now()->year;
                        $currentMonth = now()->month;
                        
                        return $query->whereHas('kehadiran', function ($query) {
                            // This is a dummy query, actual filtering will be done in collection
                        })->get()->filter(function ($user) use ($currentYear, $currentMonth) {
                            return $user->getRemainingLeaveQuotaInMonth($currentYear, $currentMonth) <= 1;
                        });
                    }),
            ])
            ->actions([
                // Action manual untuk menandai izin/sakit mendadak
                Tables\Actions\Action::make('mark_manual_leave')
                    ->label('Tandai Izin/Sakit')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(function (User $record) {
                        // Hanya tampil jika belum ada kehadiran hari ini
                        return !$record->kehadiran()
                            ->whereDate('tanggal', today('Asia/Jakarta'))
                            ->exists();
                    })
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Sakit' => '🤒 Sakit',
                                'Izin' => '📝 Izin',
                                'Alfa' => '❌ Alfa (Tanpa Keterangan)',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan detail (opsional)')
                            ->rows(3)
                            ->helperText('Contoh: Sakit demam, izin keperluan keluarga, dll.'),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->directory('manual-attendance')
                            ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png'])
                            ->maxSize(2048)
                            ->helperText('Upload surat dokter atau bukti pendukung (opsional)')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['Sakit', 'Izin'])),
                    ])
                    ->action(function (User $record, array $data) {
                        // Cek apakah sudah ada kehadiran hari ini
                        $existingAttendance = Kehadiran::where('user_id', $record->id)
                            ->whereDate('tanggal', today('Asia/Jakarta'))
                            ->first();

                        if ($existingAttendance) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Karyawan ini sudah memiliki catatan kehadiran hari ini.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Buat record kehadiran manual
                        Kehadiran::create([
                            'user_id' => $record->id,
                            'tanggal' => today('Asia/Jakarta'),
                            'status' => $data['status'],
                            'notes' => $data['notes'],
                            'metode_absen' => 'manual_hrd',
                            // Jika ada attachment, simpan path-nya di notes
                            'notes' => $data['notes'] . ($data['attachment'] ?? ''),
                        ]);

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Status kehadiran {$record->name} telah diperbarui menjadi {$data['status']}.")
                            ->success()
                            ->send();
                    }),

                // Action untuk koreksi kehadiran yang sudah ada
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

                // Action untuk melihat detail kehadiran lengkap
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
                                                'system_generated' => 'System (Cuti)',
                                                default => ucfirst($state ?? 'Unknown')
                                            })
                                            ->badge()
                                            ->color(fn (?string $state): string => match ($state) {
                                                'qrcode' => 'success',
                                                'manual_hrd' => 'warning',
                                                'system_generated' => 'info',
                                                default => 'gray'
                                            }),
                                    ])
                                    ->columns(2),

                                Components\Grid::make(2)->schema([
                                    Components\Section::make('Informasi Masuk')->schema([
                                        Components\ImageEntry::make('foto_masuk')
                                            ->disk('public')
                                            ->placeholder('Tidak ada foto')
                                            ->height(200),
                                        Components\TextEntry::make('lokasi_masuk')
                                            ->url(fn (?string $state) => $state ? "https://www.google.com/maps?q={$state}" : null, true)
                                            ->icon('heroicon-s-map-pin')
                                            ->placeholder('Tidak ada lokasi'),
                                        Components\TextEntry::make('info_perangkat_masuk')
                                            ->label('Info Perangkat')
                                            ->placeholder('Tidak ada info perangkat'),
                                    ]),
                                    Components\Section::make('Informasi Pulang')->schema([
                                        Components\ImageEntry::make('foto_pulang')
                                            ->disk('public')
                                            ->placeholder('Tidak ada foto')
                                            ->height(200),
                                        Components\TextEntry::make('lokasi_pulang')
                                            ->url(fn (?string $state) => $state ? "https://www.google.com/maps?q={$state}" : null, true)
                                            ->icon('heroicon-s-map-pin')
                                            ->placeholder('Tidak ada lokasi'),
                                        Components\TextEntry::make('info_perangkat_pulang')
                                            ->label('Info Perangkat')
                                            ->placeholder('Tidak ada info perangkat'),
                                    ]),
                                ]),
                                
                                // SECTION BARU: Info Cuti/Izin Detail
                                Components\Section::make('Informasi Cuti/Izin')
                                    ->schema([
                                        Components\TextEntry::make('notes')
                                            ->label('Catatan')
                                            ->placeholder('Tidak ada catatan'),

                                        // Info leave request jika status cuti
                                        Components\TextEntry::make('leaveRequest.leave_type_name')
                                            ->label('Jenis Cuti')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),

                                        Components\TextEntry::make('leaveRequest.reason')
                                            ->label('Alasan Cuti')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),

                                        Components\TextEntry::make('leaveRequest.start_date')
                                            ->label('Cuti Mulai')
                                            ->date('d M Y')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),

                                        Components\TextEntry::make('leaveRequest.end_date')
                                            ->label('Cuti Selesai')
                                            ->date('d M Y')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),

                                        Components\TextEntry::make('leaveRequest.approver.name')
                                            ->label('Disetujui Oleh')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),

                                        Components\TextEntry::make('leaveRequest.approved_at')
                                            ->label('Tanggal Persetujuan')
                                            ->dateTime('d M Y H:i')
                                            ->visible(fn ($record) => $record?->status === 'Cuti' && $record?->leaveRequest),
                                    ])->columns(2)
                                    ->visible(fn ($record) => in_array($record?->status, ['Cuti', 'Sakit', 'Izin'])),
                            ]);
                    })
                    ->visible(fn (User $record) => $record->kehadiran->isNotEmpty()),

                // Action untuk melihat laporan bulanan
                Tables\Actions\Action::make('view_monthly')
                    ->label('Laporan Bulanan')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn (User $record): string => static::getUrl('view', ['record' => $record->id])),

                // Action untuk melihat riwayat cuti
                Tables\Actions\Action::make('view_leave_history')
                    ->label('Riwayat Cuti')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->url(function (User $record): string {
                        return \App\Filament\Hrd\Resources\LeaveRequestResource::getUrl('index', [
                            'tableFilters' => [
                                'user' => ['value' => $record->id]
                            ]
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_all_present')
                    ->label('Tandai Hadir Semua')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Semua Karyawan Hadir')
                    ->modalDescription('Aksi ini akan menandai semua karyawan terpilih sebagai "Tepat Waktu" hari ini. Hanya karyawan yang belum memiliki record kehadiran yang akan diproses.')
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