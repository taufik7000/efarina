<?php
// app/Filament/Resources/BudgetAllocationResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetAllocationResource\Pages;
use App\Models\BudgetAllocation;
use App\Models\BudgetCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetAllocationResource extends Resource
{
    protected static ?string $model = BudgetAllocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Budget Management';
    protected static ?string $navigationLabel = 'Alokasi';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Alokasi Budget')
                    ->schema([
                        Forms\Components\Select::make('budget_plan_id')
                            ->label('Budget Plan')
                            ->relationship('budgetPlan', 'nama_budget')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('budget_category_id')
                            ->label('Kategori Budget')
                            ->relationship('category', 'nama_kategori')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('budget_subcategory_id', null)),

                        Forms\Components\Select::make('budget_subcategory_id')
                            ->label('Subkategori Budget')
                            ->options(fn (Forms\Get $get): array => 
                                $get('budget_category_id') 
                                    ? BudgetCategory::find($get('budget_category_id'))
                                        ?->subcategories()
                                        ->active()
                                        ->pluck('nama_subkategori', 'id')
                                        ->toArray() ?? []
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->placeholder('Pilih kategori terlebih dahulu'),

                        Forms\Components\TextInput::make('allocated_amount')
                            ->label('Jumlah Alokasi')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->rules(['min:0'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, $record) {
                                // Validasi tidak melebihi remaining budget (hanya saat create)
                                $budgetPlanId = $get('budget_plan_id');
                                if ($budgetPlanId && $state && !$record) { // Hanya validasi saat create
                                    $budgetPlan = \App\Models\BudgetPlan::find($budgetPlanId);
                                    if ($budgetPlan && $state > $budgetPlan->remaining_budget) {
                                        $set('allocated_amount', $budgetPlan->remaining_budget);
                                    }
                                }
                            }),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Penggunaan')
                    ->schema([
                        Forms\Components\TextInput::make('used_amount')
                            ->label('Jumlah Terpakai')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->default(0)
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('Sisa Alokasi')
                            ->prefix('Rp')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('usage_percentage')
                            ->label('Persentase Penggunaan')
                            ->suffix('%')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('budgetPlan.nama_budget')
                    ->label('Budget Plan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('budget_subcategory.nama_subkategori')
                    ->label('Jenis Alokasi')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('allocated_amount')
                    ->label('Alokasi')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_amount')
                    ->label('Terpakai')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('Persentase')
                    ->suffix('%')
                    ->color(function ($state) {
                        if ($state >= 100) return 'danger';
                        if ($state >= 90) return 'warning';
                        if ($state >= 70) return 'info';
                        return 'success';
                    })
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('budget_plan_id')
                    ->label('Budget Plan')
                    ->relationship('budgetPlan', 'nama_budget')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('budget_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_usage')
                    ->label('Penggunaan Tinggi (>90%)')
                    ->query(fn ($query) => $query->whereRaw('(used_amount / allocated_amount) * 100 >= 90')),

                Tables\Filters\Filter::make('over_budget')
                    ->label('Over Budget')
                    ->query(fn ($query) => $query->whereRaw('used_amount > allocated_amount')),
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur']))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('budgetPlan.nama_budget')
                    ->label('Budget Plan'),
                Tables\Grouping\Group::make('category.nama_kategori')
                    ->label('Kategori'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\BudgetAllocationResource\RelationManagers\TransaksisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetAllocations::route('/'),
            'create' => Pages\CreateBudgetAllocation::route('/create'),
            'view' => Pages\ViewBudgetAllocation::route('/{record}'),
            'edit' => Pages\EditBudgetAllocation::route('/{record}/edit'),
        ];
    }

}