<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\HolidayResource\Pages; // Perhatikan penyesuaian path ini
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Mengganti ikon agar lebih sesuai
    protected static ?string $navigationGroup = 'Manajemen Absensi'; // Menyesuaikan grup
    protected static ?string $navigationLabel = 'Hari Libur Nasional';
    protected static ?string $pluralModelLabel = 'Hari Libur Nasional';
    protected static ?int $navigationSort = 4; // Mengatur urutan di menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Hari Libur')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    // Pastikan tanggal unik, kecuali untuk record yang sedang diedit
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('l, d F Y') // Format tanggal lebih informatif
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Anda bisa menambahkan filter berdasarkan tahun jika diperlukan nanti
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc'); // Urutkan berdasarkan tanggal terbaru
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}