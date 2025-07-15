<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KehadiranResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class KehadiranResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Kehadiran Hari Ini';
    protected static ?string $pluralModelLabel = 'Kehadiran Hari Ini';
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
                    ->sortable(),
                
                // --- PERBAIKAN PADA KOLOM ---
                // Kita gunakan state() untuk secara eksplisit mengambil data dari relasi
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->jam_masuk)
                    ->time('H:i:s')
                    ->placeholder('Belum Absen'),
                
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->jam_pulang)
                    ->time('H:i:s')
                    ->placeholder('--'),

                Tables\Columns\TextColumn::make('status')
                    ->state(fn (User $record): ?string => $record->kehadiran->first()?->status)
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Belum Masuk'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // --- PERBAIKAN PADA MODAL ---
                Tables\Actions\Action::make('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Kehadiran')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->infolist(function (Infolist $infolist) {
                        return $infolist
                            ->record($infolist->getRecord()->kehadiran->first()) // Eksplisit set record untuk infolist
                            ->schema([
                                Components\Grid::make(2)->schema([
                                    Components\Section::make('Informasi Masuk')->schema([
                                        Components\ImageEntry::make('foto_masuk')->disk('public')->placeholder('Tidak ada foto'),
                                        Components\TextEntry::make('lokasi_masuk')->url(fn (?string $state) => $state ? "https://www.google.com/maps?q={$state}" : null, true)->icon('heroicon-s-map-pin'),
                                        Components\TextEntry::make('info_perangkat_masuk'),
                                    ]),
                                    Components\Section::make('Informasi Pulang')->schema([
                                        Components\ImageEntry::make('foto_pulang')->disk('public')->placeholder('Tidak ada foto'),
                                        Components\TextEntry::make('lokasi_pulang')->url(fn (?string $state) => $state ? "https://www.google.com/maps?q={$state}" : null, true)->icon('heroicon-s-map-pin'),
                                        Components\TextEntry::make('info_perangkat_pulang'),
                                    ]),
                                ])
                            ]);
                    })
                    // Hanya tampilkan tombol jika ada data kehadiran
                    ->visible(fn (User $record) => $record->kehadiran->isNotEmpty()),

                Tables\Actions\Action::make('view_monthly')
                    ->label('Laporan Bulanan')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn (User $record): string => static::getUrl('view', ['record' => $record->id]))
            ])
            ->bulkActions([]);
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
}