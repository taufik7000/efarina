<?php
// app/Filament/Resources/BudgetSubcategoryResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetSubcategoryResource\Pages;
use App\Models\BudgetSubcategory;
use App\Models\BudgetCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetSubcategoryResource extends Resource
{
    protected static ?string $model = BudgetSubcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Budget Management';
    protected static ?string $navigationLabel = 'Budget Subcategories';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Subkategori Budget')
                    ->schema([
                        Forms\Components\Select::make('budget_category_id')
                            ->label('Kategori Budget')
                            ->relationship('category', 'nama_kategori')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_kategori')
                                    ->label('Nama Kategori')
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('nama_subkategori')
                            ->label('Nama Subkategori')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_subkategori')
                    ->label('Nama Subkategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('budget_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetSubcategories::route('/'),
            'create' => Pages\CreateBudgetSubcategory::route('/create'),
            'edit' => Pages\EditBudgetSubcategory::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']);
    }
}