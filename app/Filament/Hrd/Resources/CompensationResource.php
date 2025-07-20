<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\CompensationResource\Pages;
use App\Models\Compensation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CompensationResource extends Resource
{
    protected static ?string $model = Compensation::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Manajemen Absensi';
    protected static ?string $navigationLabel = 'Kompensasi Libur';
    protected static ?string $pluralModelLabel = 'Kompensasi Libur';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kerja di Hari Libur')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Karyawan')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('work_date')
                            ->label('Tanggal Kerja')
                            ->required()
                            ->helperText('Tanggal kerja di hari libur (biasanya Minggu)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $date = Carbon::parse($state);
                                    if ($date->dayOfWeek !== Carbon::SUNDAY) {
                                        Notification::make()
                                            ->title('Peringatan')
                                            ->body('Tanggal yang dipilih bukan hari Minggu. Pastikan ini adalah hari libur.')
                                            ->warning()
                                            ->send();
                                    }
                                    
                                    // Auto set expires_at (90 hari ke depan)
                                    $set('expires_at', $date->copy()->addDays(90));
                                }
                            }),

                        Forms\Components\TimePicker::make('work_start_time')
                            ->label('Jam Mulai Kerja')
                            ->required(),

                        Forms\Components\TimePicker::make('work_end_time')
                            ->label('Jam Selesai Kerja')
                            ->required()
                            ->after('work_start_time'),

                        Forms\Components\TextInput::make('work_hours')
                            ->label('Total Jam Kerja')
                            ->numeric()
                            ->step(0.5)
                            ->minValue(1)
                            ->maxValue(12)
                            ->helperText('Akan dihitung otomatis jika kosong'),

                        Forms\Components\Textarea::make('work_reason')
                            ->label('Alasan Kerja di Hari Libur')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Project urgent, maintenance sistem, acara khusus, dll'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan Kompensasi')
                    ->schema([
                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->required()
                            ->minDate(now())
                            ->helperText('Kompensasi harus digunakan sebelum tanggal ini'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'earned' => 'Tersedia',
                                'used' => 'Sudah Digunakan',
                                'expired' => 'Kadaluarsa',
                            ])
                            ->default('earned')
                            ->required(),

                        Forms\Components\DatePicker::make('compensation_date')
                            ->label('Tanggal Kompensasi Digunakan')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'used'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->placeholder('Catatan tambahan (opsional)'),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('work_date')
                    ->label('Tanggal Kerja')
                    ->date('d M Y')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $dayName = Carbon::parse($record->work_date)->translatedFormat('l');
                        return $record->work_date->format('d M Y') . " ({$dayName})";
                    }),

                Tables\Columns\TextColumn::make('work_hours')
                    ->label('Jam Kerja')
                    ->suffix(' jam')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'earned' => 'Tersedia',
                        'used' => 'Digunakan',
                        'expired' => 'Kadaluarsa',
                        default => ucfirst($state)
                    })
                    ->colors([
                        'success' => 'earned',
                        'info' => 'used',
                        'danger' => 'expired',
                    ]),

                Tables\Columns\TextColumn::make('compensation_date')
                    ->label('Tgl Kompensasi')
                    ->date('d M Y')
                    ->placeholder('Belum digunakan'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->expires_at->isPast() ? 'danger' : 
                        ($record->expires_at->diffInDays() <= 7 ? 'warning' : 'gray')),

                Tables\Columns\TextColumn::make('work_reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->work_reason;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'earned' => 'Tersedia',
                        'used' => 'Digunakan',
                        'expired' => 'Kadaluarsa',
                    ]),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Akan Kadaluarsa (7 hari)')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query->where('status', 'earned')
                                    ->where('expires_at', '<=', now()->addDays(7))
                                    ->where('expires_at', '>', now());
                    }),

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
                                fn (Builder $query, $date): Builder => $query->whereDate('work_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('work_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('use_compensation')
                    ->label('Gunakan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Compensation $record) => $record->canBeUsed())
                    ->form([
                        Forms\Components\DatePicker::make('compensation_date')
                            ->label('Tanggal Kompensasi')
                            ->required()
                            ->helperText('Tanggal tidak masuk kerja sebagai kompensasi')
                            ->minDate(now()),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Alasan menggunakan kompensasi (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (Compensation $record, array $data) {
                        $compensationDate = Carbon::parse($data['compensation_date']);
                        
                        // Validasi tidak bentrok dengan kehadiran existing
                        $existingAttendance = \App\Models\Kehadiran::where('user_id', $record->user_id)
                            ->whereDate('tanggal', $compensationDate)
                            ->first();
                        
                        if ($existingAttendance) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Sudah ada catatan kehadiran untuk tanggal tersebut.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        if ($record->use($compensationDate, $data['notes'])) {
                            Notification::make()
                                ->title('Kompensasi Digunakan')
                                ->body("Kompensasi berhasil digunakan untuk tanggal {$compensationDate->format('d M Y')}.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Kompensasi tidak bisa digunakan.')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_expired')
                    ->label('Tandai Kadaluarsa')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->markExpired()) {
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title('Bulk Update Selesai')
                            ->body("{$count} kompensasi berhasil ditandai kadaluarsa.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompensation::route('/'),
            'create' => Pages\CreateCompensation::route('/create'),
            'edit' => Pages\EditCompensation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $expiring = Compensation::where('status', 'earned')
            ->where('expires_at', '<=', now()->addDays(7))
            ->where('expires_at', '>', now())
            ->count();

        return $expiring > 0 ? (string) $expiring : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}