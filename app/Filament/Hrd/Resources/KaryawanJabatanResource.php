<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KaryawanJabatanResource\Pages;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KaryawanJabatanResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Organisasi';
    protected static ?string $navigationLabel = 'Atur Jabatan Karyawan';
    protected static ?string $pluralModelLabel = 'Jabatan Karyawan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Karyawan')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\Select::make('jabatan_id')
                    ->relationship('jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->label('Jabatan'),

                // --- PERBAIKAN DI SINI ---
                // Logika diubah agar lebih aman dan tidak menyebabkan error
                Forms\Components\TextInput::make('divisi')
                    ->label('Divisi')
                    ->disabled()
                    ->placeholder(function (Forms\Get $get) {
                        $jabatanId = $get('jabatan_id');
                        
                        // Jika tidak ada jabatan yang dipilih, tampilkan pesan default
                        if (!$jabatanId) {
                            return 'Pilih jabatan terlebih dahulu';
                        }
                        
                        // Cari jabatan dan ambil nama divisinya dengan aman
                        $jabatan = \App\Models\Jabatan::find($jabatanId);
                        
                        // Menggunakan null-safe operator (?) untuk mencegah error jika relasi tidak ditemukan
                        return $jabatan?->divisi?->nama_divisi ?? 'Divisi tidak ditemukan';
                    })
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan Saat Ini')
                    ->placeholder('Belum diatur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan.divisi.nama_divisi')
                    ->label('Divisi')
                    ->placeholder('Belum diatur')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisi')
                    ->relationship('jabatan.divisi', 'nama_divisi')
                    ->label('Filter Berdasarkan Divisi'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah Jabatan'),
            ])
            ->bulkActions([]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawanJabatans::route('/'),
            'edit' => Pages\EditKaryawanJabatan::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}