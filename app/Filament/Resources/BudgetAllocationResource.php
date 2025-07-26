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
                // ===============================================
                // GRID 2 KOLOM UTAMA - DIPERBAIKI
                // ===============================================
                Forms\Components\Grid::make(2) // Lebih sederhana
                    ->schema([
                        // ===============================================
                        // KOLOM KIRI: Informasi Alokasi Budget
                        // ===============================================
                        Forms\Components\Section::make('📊 Informasi Alokasi Budget')
                            ->description('Detail alokasi budget dan kategori')
                            ->icon('heroicon-o-chart-pie')
                            ->columnSpan(1) // Eksplisit set column span
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
                                        if ($budgetPlanId && $state && !$record) {
                                            $budgetPlan = \App\Models\BudgetPlan::find($budgetPlanId);
                                            if ($budgetPlan && $state > $budgetPlan->remaining_budget) {
                                                $set('allocated_amount', $budgetPlan->remaining_budget);
                                            }
                                        }
                                    }),

                                // Informasi Audit
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('created_at')
                                            ->label('Tanggal Dibuat')
                                            ->disabled()
                                            ->visible(fn ($record) => $record !== null),

                                        Forms\Components\Select::make('created_by')
                                            ->label('Dibuat Oleh')
                                            ->relationship('createdBy', 'name')
                                            ->disabled()
                                            ->visible(fn ($record) => $record !== null),
                                    ])
                                    ->visible(fn ($record) => $record !== null),
                            ]),

                        // ===============================================
                        // KOLOM KANAN: Informasi Penggunaan & Status
                        // ===============================================
                        Forms\Components\Section::make('💰 Informasi Penggunaan')
                            ->description('Status penggunaan dan sisa budget')
                            ->icon('heroicon-o-banknotes')
                            ->columnSpan(1) // Eksplisit set column span
                            ->schema([
                                // Grid untuk nilai-nilai penggunaan
                                Forms\Components\Grid::make(1)
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
                                    ]),

                                // Status Badge
                                Forms\Components\Placeholder::make('status_info')
                                    ->label('Status Budget')
                                    ->content(function ($record) {
                                        if (!$record) return 'Belum ada data';
                                        
                                        $percentage = $record->usage_percentage ?? 0;
                                        
                                        if ($percentage >= 95) {
                                            return '🔴 Sangat Kritis (≥95%)';
                                        } elseif ($percentage >= 90) {
                                            return '🟠 Kritis (≥90%)';
                                        } elseif ($percentage >= 75) {
                                            return '🟡 Perhatian (≥75%)';
                                        } else {
                                            return '🟢 Aman (<75%)';
                                        }
                                    })
                                    ->visible(fn ($record) => $record !== null),

                                Forms\Components\DateTimePicker::make('updated_at')
                                    ->label('Terakhir Diupdate')
                                    ->disabled()
                                    ->visible(fn ($record) => $record !== null),

                                // ===============================================
                                // Ringkasan Budget dalam satu kotak kecil
                                // ===============================================
                                Forms\Components\Section::make('📈 Ringkasan')
                                    ->schema([
                                        Forms\Components\Grid::make(1)
                                            ->schema([
                                                Forms\Components\Placeholder::make('allocated_summary')
                                                    ->label('💰 Total Alokasi')
                                                    ->content(function ($record) {
                                                        if (!$record) return 'Rp 0';
                                                        return 'Rp ' . number_format($record->allocated_amount ?? 0, 0, ',', '.');
                                                    }),

                                                Forms\Components\Placeholder::make('used_summary')
                                                    ->label('📉 Sudah Terpakai')
                                                    ->content(function ($record) {
                                                        if (!$record) return 'Rp 0';
                                                        return 'Rp ' . number_format($record->used_amount ?? 0, 0, ',', '.');
                                                    }),

                                                Forms\Components\Placeholder::make('remaining_summary')
                                                    ->label('📈 Sisa Budget')
                                                    ->content(function ($record) {
                                                        if (!$record) return 'Rp 0';
                                                        $remaining = ($record->allocated_amount ?? 0) - ($record->used_amount ?? 0);
                                                        $color = $remaining < 0 ? '🔴' : '🟢';
                                                        return $color . ' Rp ' . number_format($remaining, 0, ',', '.');
                                                    }),
                                            ]),
                                    ])
                                    ->visible(fn ($record) => $record !== null)
                                    ->compact(),
                            ]),
                    ]),

                // ===============================================
                // CATATAN - FULL WIDTH (di bawah 2 kolom)
                // ===============================================
                Forms\Components\Section::make('📝 Catatan')
                    ->description('Catatan tambahan untuk alokasi budget ini')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Tambahkan catatan untuk alokasi budget ini...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record && empty($record->catatan))
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('subcategory.nama_subkategori')
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