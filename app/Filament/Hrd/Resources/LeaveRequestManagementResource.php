<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\LeaveRequestManagementResource\Pages;
use App\Models\Compensation;
use App\Models\Kehadiran;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestManagementResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Absensi';
    protected static ?string $navigationLabel = 'Persetujuan Cuti';
    protected static ?string $pluralModelLabel = 'Persetujuan Cuti';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pengaju')->searchable(),
                Tables\Columns\TextColumn::make('leave_type')->label('Jenis Cuti')->badge(),
                Tables\Columns\TextColumn::make('start_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('total_days')->label('Durasi')->suffix(' hari'),
                Tables\Columns\TextColumn::make('replacementUser.name')->label('Pengganti')->placeholder('N/A'),
                Tables\Columns\TextColumn::make('replacement_status')->label('Status Pengganti')->badge()
                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
                Tables\Columns\TextColumn::make('status')->label('Status Akhir')->badge()
                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])->default('pending'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // --- AKSI PERSETUJUAN OLEH HRD ---
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->action(function (LeaveRequest $record) {
                        // Cek jika ada pengganti dan belum disetujui
                        if ($record->replacement_user_id && $record->replacement_status !== 'approved') {
                            Notification::make()
                                ->title('Tidak Bisa Disetujui')
                                ->body('Pengganti belum menyetujui permintaan.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Update status
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now()
                        ]);

                        // Buat kompensasi untuk pengganti jika ada
                        if ($record->replacement_user_id) {
                            Compensation::create([
                                'user_id' => $record->replacement_user_id,
                                'work_date' => $record->start_date,
                                'work_hours' => 8,
                                'work_reason' => 'Kompensasi menggantikan cuti ' . $record->user->name,
                                'status' => 'earned',
                                'expires_at' => Carbon::now()->endOfMonth(),
                                'created_by' => auth()->id()
                            ]);
                        }

                        // Update kehadiran
                        for ($date = $record->start_date->copy(); $date->lte($record->end_date); $date->addDay()) {
                            Kehadiran::updateOrCreate(
                                ['user_id' => $record->user_id, 'tanggal' => $date],
                                ['status' => 'Cuti', 'leave_request_id' => $record->id]
                            );
                        }

                        // KIRIM NOTIFIKASI KE PENGAJU menggunakan Filament native
                        \Filament\Notifications\Notification::make()
                            ->title('Cuti Disetujui')
                            ->body("Pengajuan {$record->leave_type} Anda telah disetujui")
                            ->icon('heroicon-o-check-circle')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('Lihat Detail')
                                    ->url(url('/team/pengajuan-cuti'))  // URL untuk panel team/employee
                            ])
                            ->sendToDatabase($record->user);

                        // KIRIM NOTIFIKASI KE PENGGANTI JIKA ADA
                        if ($record->replacement_user_id) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cuti Disetujui - Anda Ditunjuk Pengganti')
                                ->body("Cuti {$record->user->name} disetujui. Anda ditunjuk sebagai pengganti")
                                ->icon('heroicon-o-user-plus')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('Lihat Detail')
                                        ->url(url('/team/pengajuan-cuti'))
                                ])
                                ->sendToDatabase($record->replacementUser);
                        }

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pengajuan cuti telah disetujui.')
                            ->success()
                            ->send();
                    }),

                // --- AKSI PENOLAKAN OLEH HRD ---
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now()
                        ]);

                        // KIRIM NOTIFIKASI KE PENGAJU
                        \Filament\Notifications\Notification::make()
                            ->title('Cuti Ditolak')
                            ->body("Pengajuan {$record->leave_type} Anda ditolak: {$data['rejection_reason']}")
                            ->icon('heroicon-o-x-circle')
                            ->danger()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('Lihat Detail')
                                    ->url(url('/team/pengajuan-cuti'))
                            ])
                            ->sendToDatabase($record->user);

                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequestManagement::route('/'),
        ];
    }

    /**
     * Badge untuk navigation - tampilkan jumlah pending
     */
    public static function getNavigationBadge(): ?string
    {
        $count = LeaveRequest::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}