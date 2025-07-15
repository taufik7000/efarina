<?php
// app/Filament/Resources/BudgetPeriodResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetPeriodResource\Pages;
use App\Models\BudgetPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;

class BudgetPeriodResource extends Resource
{
    protected static ?string $model = BudgetPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Budget Management';
    protected static ?string $navigationLabel = 'Periode';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode Budget')
                    ->schema([
                        Forms\Components\TextInput::make('nama_periode')
                            ->label('Nama Periode')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budget Q1 2024'),

                        Forms\Components\Select::make('type')
                            ->label('Tipe Periode')
                            ->options([
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Kuartalan',
                                'yearly' => 'Tahunan',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $startDate = $get('tanggal_mulai');
                                if ($startDate && $state) {
                                    $start = Carbon::parse($startDate);
                                    $endDate = match($state) {
                                        'monthly' => $start->copy()->endOfMonth(),
                                        'quarterly' => $start->copy()->addMonths(3)->subDay(),
                                        'yearly' => $start->copy()->endOfYear(),
                                        default => $start->copy()->addMonth()
                                    };
                                    $set('tanggal_selesai', $endDate->format('Y-m-d'));
                                }
                            }),

                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $type = $get('type');
                                if ($state && $type) {
                                    $start = Carbon::parse($state);
                                    $endDate = match($type) {
                                        'monthly' => $start->copy()->endOfMonth(),
                                        'quarterly' => $start->copy()->addMonths(3)->subDay(),
                                        'yearly' => $start->copy()->endOfYear(),
                                        default => $start->copy()->addMonth()
                                    };
                                    $set('tanggal_selesai', $endDate->format('Y-m-d'));
                                }
                            }),

                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->after('tanggal_mulai'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'closed' => 'Ditutup',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_periode')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($record) => $record->type_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Bulanan',
                        'quarterly' => 'Kuartalan', 
                        'yearly' => 'Tahunan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'closed' => 'Ditutup',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('budgetPlans_count')
                    ->label('Budget Plans')
                    ->counts('budgetPlans')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_current')
                    ->label('Periode Aktif')
                    ->getStateUsing(fn ($record) => $record->isCurrentPeriod())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Periode')
                    ->options([
                        'monthly' => 'Bulanan',
                        'quarterly' => 'Kuartalan',
                        'yearly' => 'Tahunan',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'closed' => 'Ditutup',
                    ]),

                Tables\Filters\Filter::make('current_period')
                    ->label('Periode Saat Ini')
                    ->query(fn ($query) => $query->current()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole([ 'direktur', 'keuangan'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole([ 'direktur']))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
                ]),
            ])
            ->defaultSort('tanggal_mulai', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetPeriods::route('/'),
            'create' => Pages\CreateBudgetPeriod::route('/create'),
            'edit' => Pages\EditBudgetPeriod::route('/{record}/edit'),
        ];
    }
}