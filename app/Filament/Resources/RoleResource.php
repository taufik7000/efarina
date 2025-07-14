<?php
namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role; // <-- PASTIKAN MENGGUNAKAN MODEL ROLE DARI SPATIE

class RoleResource extends Resource
{
    protected static ?string $model = Role::class; // <-- GUNAKAN MODEL ROLE

    protected static ?string $navigationIcon = 'heroicon-o-finger-print'; // Ganti ikon jika mau
    protected static ?string $navigationGroup = 'Settings'; // Kelompokkan menu navigasi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true) // Pastikan nama role unik
                    ->maxLength(255),
                // Jika Anda ingin mengelola permission juga, tambahkan Select relationship di sini
                // Forms\Components\Select::make('permissions')
                //    ->multiple()
                //    ->relationship('permissions', 'name')
                //    ->preload()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}