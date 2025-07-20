<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\LeaveRequestManagementResource\Pages;
use App\Models\Compensation;
use App\Models\Kehadiran;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
                // --- INI ADALAH VIEW RESOURCE YANG ANDA MINTA ---
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->infolist([
                        InfolistSection::make('Informasi Pengajuan Cuti')
                            ->schema([
                                TextEntry::make('user.name')->label('Nama Pengaju'),
                                TextEntry::make('user.jabatan.nama_jabatan')->label('Jabatan'),
                                TextEntry::make('leave_type')->label('Jenis Cuti')->badge(),
                                TextEntry::make('start_date')->label('Tanggal Mulai')->date('l, d F Y'),
                                TextEntry::make('end_date')->label('Tanggal Selesai')->date('l, d F Y'),
                                TextEntry::make('total_days')->label('Total Hari')->suffix(' hari'),
                                TextEntry::make('reason')->label('Alasan Pengajuan')->columnSpanFull(),
                                // Tambahkan link untuk download lampiran jika ada
                                TextEntry::make('attachment')->label('Lampiran')->url(fn ($record) => $record->attachment ? \Illuminate\Support\Facades\Storage::url($record->attachment) : null, true)->visible(fn ($record) => !empty($record->attachment)),
                            ])->columns(2),

                        InfolistSection::make('Informasi Pengganti & Persetujuan')
                            ->schema([
                                TextEntry::make('replacementUser.name')->label('Pengganti')->placeholder('Tidak ada pengganti'),
                                TextEntry::make('replacement_status')->label('Status Persetujuan Pengganti')->badge()
                                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
                                TextEntry::make('status')->label('Status Akhir Cuti')->badge()
                                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
                                TextEntry::make('approver.name')->label('Diproses oleh (HRD)')->placeholder('Belum ada tindakan'),
                                TextEntry::make('action_at')->label('Tanggal Diproses')->dateTime()->placeholder('N/A'),
                                TextEntry::make('rejection_reason')->label('Alasan Penolakan')->visible(fn ($state) => !empty($state))->columnSpanFull(),
                            ])->columns(2),
                    ]),

                // --- AKSI PERSETUJUAN OLEH HRD ---
                Tables\Actions\Action::make('approve')->label('Setujui')->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->action(function (LeaveRequest $record) {
                        DB::transaction(function () use ($record) {
                            if ($record->replacement_user_id && $record->replacement_status !== 'approved') {
                                Notification::make()->title('Gagal')->body('Persetujuan dari pengganti masih pending.')->danger()->send();
                                return;
                            }
                            $record->update(['status' => 'approved', 'approved_by' => auth()->id(), 'action_at' => now()]);
                            if ($record->replacement_user_id) {
                                Compensation::create(['user_id' => $record->replacement_user_id, 'work_date' => $record->start_date, 'work_hours' => 8, 'work_reason' => 'Kompensasi menggantikan cuti ' . $record->user->name, 'status' => 'earned', 'expires_at' => Carbon::now()->endOfMonth(), 'created_by' => auth()->id()]);
                            }
                            for ($date = $record->start_date->copy(); $date->lte($record->end_date); $date->addDay()) {
                                Kehadiran::updateOrCreate(['user_id' => $record->user_id, 'tanggal' => $date], ['status' => 'Cuti', 'leave_request_id' => $record->id]);
                            }
                            Notification::make()->title('Berhasil')->body('Pengajuan cuti telah disetujui.')->success()->send();
                        });
                    }),

                // --- AKSI PENOLAKAN OLEH HRD ---
                Tables\Actions\Action::make('reject')->label('Tolak')->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->form([Forms\Components\Textarea::make('rejection_reason')->label('Alasan Penolakan')->required()])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason'], 'approved_by' => auth()->id(), 'action_at' => now()]);
                        Notification::make()->title('Pengajuan Ditolak')->warning()->send();
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
}