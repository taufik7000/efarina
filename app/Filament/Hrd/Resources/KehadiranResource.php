<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KehadiranResource\Pages;
use App\Models\User; // Menggunakan model User
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KehadiranResource extends Resource
{
    protected static ?string $model = User::class; // Model dasar adalah User
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
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

                Tables\Columns\TextColumn::make('kehadiran.jam_masuk')
                    ->time('H:i:s')
                    ->label('Jam Masuk')
                    ->placeholder('Belum Absen Masuk'),

                Tables\Columns\TextColumn::make('kehadiran.jam_pulang')
                    ->time('H:i:s')
                    ->label('Jam Pulang')
                    ->placeholder('--'),

                Tables\Columns\TextColumn::make('kehadiran.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Belum Masuk'),
            ])
            ->filters([])
            ->actions([
                // --- PERBAIKAN DI SINI ---
                // Baris TextColumn yang salah telah dihapus dari array ini
                Tables\Actions\Action::make('view_monthly')
                    ->label('Lihat Laporan Bulanan')
                    ->icon('heroicon-o-eye')
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