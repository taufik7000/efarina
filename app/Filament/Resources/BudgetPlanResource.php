<?php
// app/Filament/Resources/BudgetPlanResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetPlanResource\Pages;
use App\Models\BudgetPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class BudgetPlanResource extends Resource
{
    protected static ?string $model = BudgetPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Budget Management';
    protected static ?string $navigationLabel = 'Rencana';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Budget Plan')
                    ->schema([
                        Forms\Components\Select::make('budget_period_id')
                            ->label('Periode Budget')
                            ->relationship('period', 'nama_periode')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_periode')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'monthly' => 'Bulanan',
                                        'quarterly' => 'Kuartalan',
                                        'yearly' => 'Tahunan',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('tanggal_mulai')->required(),
                                Forms\Components\DatePicker::make('tanggal_selesai')->required(),
                            ]),

                        Forms\Components\TextInput::make('nama_budget')
                            ->label('Nama Budget')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budget Operasional Q1 2024'),

                        Forms\Components\TextInput::make('total_budget')
                            ->label('Total Budget')
                            ->required()
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->placeholder('0')
                            ->extraInputAttributes([
                                'oninput' => "
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        ",
                                'onkeydown' => "
            if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();
            }
        "
                            ])
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->rules(['min:0'])
                            ->helperText('Masukkan nominal dalam rupiah. Contoh: 1.000.000 untuk satu juta rupiah'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'approved' => 'Disetujui',
                                'active' => 'Aktif',
                                'closed' => 'Ditutup',
                            ])
                            ->required()
                            ->default('draft')
                            ->disabled(fn (string $context) => $context === 'edit'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Penggunaan Budget')
                    ->schema([
                        Forms\Components\TextInput::make('total_allocated')
                            ->label('Total Dialokasikan')
                            ->prefix('Rp')
                            ->disabled()
                            ->placeholder('Akan dihitung otomatis dari alokasi'),

                        Forms\Components\TextInput::make('total_used')
                            ->label('Total Terpakai')
                            ->prefix('Rp')
                            ->disabled()
                            ->placeholder('Akan dihitung otomatis dari transaksi'),

                        Forms\Components\TextInput::make('remaining_budget')
                            ->label('Sisa Budget')
                            ->prefix('Rp')
                            ->disabled()
                            ->placeholder('Total Budget - Total Dialokasikan'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
                Forms\Components\Section::make('Informasi Approval')
                    ->schema([
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tanggal Approval')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->approved_at),

                        Forms\Components\Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->approved_by),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && ($record->approved_at || $record->approved_by)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_budget')
                    ->label('Nama Budget')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('period.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_budget')
                    ->label('Total Budget')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_allocated')
                    ->label('Total Alokasi')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_used')
                    ->label('Total Terpakai')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_budget')
                    ->label('Sisa Budget')
                    ->money('IDR')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('allocation_percentage')
                    ->label('% Alokasi')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 100 ? 'danger' : ($state >= 80 ? 'warning' : 'success')),

                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('% Penggunaan')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 90 ? 'danger' : ($state >= 75 ? 'warning' : 'success')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'approved' => 'Disetujui',
                        'active' => 'Aktif',
                        'closed' => 'Ditutup',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('Belum disetujui')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('budget_period_id')
                    ->label('Periode')
                    ->relationship('period', 'nama_periode')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Disetujui',
                        'active' => 'Aktif',
                        'closed' => 'Ditutup',
                    ]),

                Tables\Filters\Filter::make('needs_approval')
                    ->label('Perlu Approval')
                    ->query(fn ($query) => $query->where('status', 'draft')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft' && 
                             auth()->user()->hasRole(['admin', 'super-admin', 'direktur']))
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Budget Plan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui budget plan ini?')
                    ->action(function ($record) {
                        $record->approve(auth()->id());
                        Notification::make()
                            ->title('Budget Plan Disetujui')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']) &&
                             in_array($record->status, ['draft', 'approved'])),
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
        'index' => Pages\ListBudgetPlans::route('/'),
        'create' => Pages\CreateBudgetPlan::route('/create'),
        'view' => Pages\ViewBudgetPlan::route('/{record}'),
        'edit' => Pages\EditBudgetPlan::route('/{record}/edit'),
    ];
}
}