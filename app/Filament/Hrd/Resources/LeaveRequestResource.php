<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Absensi Karyawan';
    protected static ?string $navigationLabel = 'Pengajuan Cuti';
    protected static ?string $pluralModelLabel = 'Pengajuan Cuti';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Karyawan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Karyawan')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $user = User::find($state);
                                    $currentMonth = now()->month;
                                    $currentYear = now()->year;
                                    $remaining = $user?->getRemainingLeaveQuotaInMonth($currentYear, $currentMonth) ?? 0;
                                    
                                    $set('remaining_quota_info', "Sisa kuota bulan ini: {$remaining} hari");
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('remaining_quota_info')
                            ->label('Info Kuota')
                            ->content(fn ($get) => $get('remaining_quota_info') ?? 'Pilih karyawan untuk melihat kuota')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Cuti')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->minDate(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $endDate = $get('end_date');
                                if ($state && $endDate) {
                                    $leaveRequest = new LeaveRequest([
                                        'start_date' => $state,
                                        'end_date' => $endDate
                                    ]);
                                    $workingDays = $leaveRequest->calculateWorkingDays();
                                    $set('total_days', $workingDays);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $startDate = $get('start_date');
                                if ($state && $startDate) {
                                    $leaveRequest = new LeaveRequest([
                                        'start_date' => $startDate,
                                        'end_date' => $state
                                    ]);
                                    $workingDays = $leaveRequest->calculateWorkingDays();
                                    $set('total_days', $workingDays);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('total_days')
                            ->label('Total Hari Kerja')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Otomatis terhitung (tidak termasuk Minggu)')
                            ->columnSpan(2),

                        Forms\Components\Select::make('leave_type')
                            ->label('Jenis Cuti')
                            ->options([
                                'annual' => 'Cuti Tahunan',
                                'sick' => 'Sakit',
                                'emergency' => 'Darurat',
                                'maternity' => 'Melahirkan',
                                'paternity' => 'Ayah Baru',
                                'unpaid' => 'Cuti Tanpa Gaji',
                                'other' => 'Lainnya',
                            ])
                            ->required()
                            ->default('annual')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Cuti')
                            ->required()
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png'])
                            ->maxSize(5120)
                            ->helperText('Upload surat dokter atau dokumen pendukung lainnya (opsional)')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(2)
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->rows(2)
                            ->visible(fn (Forms\Get $get) => $get('status') === 'rejected')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Hari')
                    ->suffix(' hari')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('leave_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'annual' => 'Tahunan',
                        'sick' => 'Sakit',
                        'emergency' => 'Darurat',
                        'maternity' => 'Melahirkan',
                        'paternity' => 'Ayah Baru',
                        'unpaid' => 'Tanpa Gaji',
                        'other' => 'Lainnya',
                        default => ucfirst($state)
                    })
                    ->colors([
                        'primary' => 'annual',
                        'warning' => 'sick',
                        'danger' => 'emergency',
                        'success' => ['maternity', 'paternity'],
                        'gray' => ['unpaid', 'other'],
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state)
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->reason;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('leave_type')
                    ->label('Jenis Cuti')
                    ->options([
                        'annual' => 'Tahunan',
                        'sick' => 'Sakit',
                        'emergency' => 'Darurat',
                        'maternity' => 'Melahirkan',
                        'paternity' => 'Ayah Baru',
                        'unpaid' => 'Tanpa Gaji',
                        'other' => 'Lainnya',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan Cuti')
                    ->modalDescription(fn (LeaveRequest $record) => 
                        "Apakah Anda yakin ingin menyetujui cuti {$record->user->name} dari {$record->start_date->format('d M Y')} sampai {$record->end_date->format('d M Y')}?"
                    )
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(3)
                            ->placeholder('Opsional: tambahkan catatan persetujuan')
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $success = $record->approve(auth()->id(), $data['approval_notes'] ?? null);
                        
                        if ($success) {
                            Notification::make()
                                ->title('Cuti Disetujui')
                                ->body("Pengajuan cuti {$record->user->name} berhasil disetujui.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal Menyetujui')
                                ->body('Terjadi kesalahan saat menyetujui pengajuan cuti.')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan Cuti')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Berikan alasan mengapa pengajuan cuti ditolak')
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $success = $record->reject(auth()->id(), $data['rejection_reason']);
                        
                        if ($success) {
                            Notification::make()
                                ->title('Cuti Ditolak')
                                ->body("Pengajuan cuti {$record->user->name} telah ditolak.")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}