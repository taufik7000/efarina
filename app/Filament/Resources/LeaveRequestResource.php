<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationGroup = 'Absensi';
    protected static ?string $navigationLabel = 'Pengajuan Cuti';
    protected static ?string $pluralModelLabel = 'Pengajuan Cuti';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'pengajuan-cuti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengajuan')
                    ->description('Isi detail pengajuan cuti Anda. Jika memerlukan pengganti, silakan pilih rekan kerja yang bersedia.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Nama Karyawan')
                            ->relationship('user', 'name')
                            ->default(Auth::id())
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),
                        Forms\Components\Select::make('leave_type')
                            ->label('Jenis Cuti')
                            ->options([
                                'Cuti Tahunan' => 'Cuti Tahunan',
                                'Cuti Sakit' => 'Cuti Sakit',
                                'Cuti Alasan Penting' => 'Cuti Alasan Penting',
                                'Cuti Melahirkan' => 'Cuti Melahirkan'
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->required()
                            ->minDate(now()->addDays(7))
                            ->helperText('Pengajuan cuti minimal 7 hari sebelum tanggal mulai.'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->required()
                            ->minDate(fn (Forms\Get $get) => $get('start_date'))
                            ->helperText('Tanggal selesai tidak boleh sebelum tanggal mulai.'),
                        Forms\Components\Select::make('replacement_user_id')
                            ->label('Pilih Pengganti (Opsional)')
                            ->options(User::where('id', '!=', auth()->id())->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Pilih rekan kerja yang akan menggantikan Anda selama cuti.'),
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Cuti')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran (Contoh: Surat Dokter)')
                            ->directory('leave-attachments')
                            ->visibility('private')
                            ->openable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pengaju')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('leave_type')->label('Jenis Cuti')->badge(),
                Tables\Columns\TextColumn::make('start_date')->label('Mulai Cuti')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('replacementUser.name')->label('Pengganti')->placeholder('Tidak ada'),
                Tables\Columns\TextColumn::make('replacement_status')->label('Persetujuan Pengganti')->badge()
                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('status')->label('Status Akhir')->badge()
                    ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->infolist(self::getInfolistSchema()),
                Tables\Actions\EditAction::make()->visible(fn (LeaveRequest $record) => $record->status === 'pending' && $record->user_id === auth()->id()),
                
                // ACTION UNTUK PENGGANTI MENYETUJUI/MENOLAK
                Tables\Actions\Action::make('approve_replacement')
                    ->label('Setujui Penggantian')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Persetujuan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui untuk menjadi pengganti?')
                    ->visible(fn (LeaveRequest $record): bool => 
                        $record->replacement_user_id === auth()->id() && 
                        $record->replacement_status === 'pending'
                    )
                    ->action(function (LeaveRequest $record) {
                        $record->update(['replacement_status' => 'approved']);
                        
                        // KIRIM NOTIFIKASI KE PENGAJU
                        \Filament\Notifications\Notification::make()
                            ->title('Pengganti Menyetujui')
                            ->body("{$record->replacementUser->name} menyetujui menjadi pengganti Anda")
                            ->icon('heroicon-o-check-circle')
                            ->success()
                            ->sendToDatabase($record->user);
                        
                        // KIRIM NOTIFIKASI KE HRD
                        $hrdUsers = User::role(['hrd', 'admin'])->get();
                        foreach ($hrdUsers as $hrd) {
                            \Filament\Notifications\Notification::make()
                                ->title('Pengganti Disetujui')
                                ->body("Pengganti untuk cuti {$record->user->name} telah disetujui")
                                ->icon('heroicon-o-check-circle')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('review')
                                        ->label('Review Pengajuan')
                                        ->url('/hrd/leave-request-managements')
                                ])
                                ->sendToDatabase($hrd);
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('user_id', auth()->id())->orWhere('replacement_user_id', auth()->id());
                });
            });
    }

    public static function getInfolistSchema(): array
    {
        return [
            InfolistSection::make('Informasi Pengajuan')
                ->schema([
                    TextEntry::make('user.name')->label('Nama Pengaju'),
                    TextEntry::make('leave_type')->label('Jenis Cuti')->badge(),
                    TextEntry::make('start_date')->label('Tanggal Mulai')->date('l, d F Y'),
                    TextEntry::make('end_date')->label('Tanggal Selesai')->date('l, d F Y'),
                    TextEntry::make('total_days')->label('Total Hari')->suffix(' hari'),
                    TextEntry::make('reason')->label('Alasan')->columnSpanFull(),
                ])->columns(2),
            InfolistSection::make('Status Persetujuan')
                ->schema([
                    TextEntry::make('status')->label('Status Akhir')->badge()
                        ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
                    TextEntry::make('replacementUser.name')->label('Pengganti')->placeholder('Tidak ada'),
                    TextEntry::make('replacement_status')->label('Persetujuan Pengganti')->badge()
                        ->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
                    TextEntry::make('approver.name')->label('Disetujui/Ditolak oleh')->placeholder('Belum ada tindakan'),
                    TextEntry::make('approved_at')->label('Tanggal Tindakan')->dateTime(),
                    TextEntry::make('rejection_reason')->label('Alasan Penolakan')->visible(fn ($state) => !empty($state)),
                ])->columns(2),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
        ];
    }
}