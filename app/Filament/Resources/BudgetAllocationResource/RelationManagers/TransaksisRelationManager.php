<?php
// app/Filament/Resources/BudgetAllocationResource/RelationManagers/TransaksisRelationManager.php

namespace App\Filament\Resources\BudgetAllocationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransaksisRelationManager extends RelationManager
{
    protected static string $relationship = 'transaksis';
    protected static ?string $title = 'Transaksi Terkait';
    protected static ?string $recordTitleAttribute = 'nama_transaksi';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => \App\Filament\Resources\TransaksiResource::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_transaksi')
                    ->label('Nama Transaksi')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($record) => $record->jenis_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'e_wallet' => 'E-Wallet',
                        'cek' => 'Cek',
                        default => $state ?? 'Tidak ada',
                    }),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Tidak ada'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ]),

                Tables\Filters\Filter::make('tanggal_transaksi')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_transaksi', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_transaksi', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                // Tidak perlu create action karena transaksi dibuat dari TransaksiResource
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\TransaksiResource::getUrl('view', ['record' => $record])),
                
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\TransaksiResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => auth()->user()->hasRole(['admin', 'super-admin', 'keuangan']) && $record->status === 'draft'),
            ])
            ->defaultSort('tanggal_transaksi', 'desc')
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateDescription('Transaksi yang menggunakan alokasi budget ini akan ditampilkan di sini.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}