<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewTransaksi extends ViewRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']) &&
                         in_array($this->record->status, ['draft', 'pending'])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    // KOLOM UTAMA (2/3 width)
                    Group::make()
                        ->schema([
                            Section::make('Informasi Transaksi')
                                ->schema([
                                    TextEntry::make('nomor_transaksi')
                                        ->label('Nomor Transaksi')
                                        ->weight('bold')
                                        ->copyable(),

                                    TextEntry::make('nama_transaksi')
                                        ->label('Nama Transaksi')
                                        ->weight('bold'),

                                    TextEntry::make('tanggal_transaksi')
                                        ->label('Tanggal Transaksi')
                                        ->date('d F Y'),

                                    TextEntry::make('jenis_transaksi')
                                        ->label('Jenis Transaksi')
                                        ->badge()
                                        ->color(fn ($record) => $record->jenis_color)
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'pemasukan' => 'Pemasukan',
                                            'pengeluaran' => 'Pengeluaran',
                                            default => $state,
                                        }),

                                    TextEntry::make('total_amount')
                                        ->label('Total Amount')
                                        ->money('IDR')
                                        ->size('lg')
                                        ->weight('bold')
                                        ->color(fn ($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger'),

                                    TextEntry::make('deskripsi')
                                        ->label('Deskripsi')
                                        ->columnSpanFull()
                                        ->placeholder('Tidak ada deskripsi'),
                                ])
                                ->columns(2),

                            Section::make('Detail Items')
                                ->schema([
                                    RepeatableEntry::make('items')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('nama_item')
                                                ->label('Nama Item')
                                                ->weight('bold'),

                                            TextEntry::make('kuantitas')
                                                ->label('Qty')
                                                ->suffix(fn ($record) => ' ' . ($record->satuan ?? 'pcs')),

                                            TextEntry::make('harga_satuan')
                                                ->label('Harga Satuan')
                                                ->money('IDR'),

                                            TextEntry::make('subtotal')
                                                ->label('Subtotal')
                                                ->money('IDR')
                                                ->weight('bold'),

                                            TextEntry::make('deskripsi_item')
                                                ->label('Keterangan')
                                                ->columnSpanFull()
                                                ->placeholder('Tidak ada keterangan'),
                                        ])
                                        ->columns(4)
                                        ->contained(false),
                                ])
                                ->visible(fn ($record) => $record->items->count() > 0),
                            
                            Section::make('Timeline Transaksi')
                                ->schema([
                                    RepeatableEntry::make('histories')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('action_description')
                                                ->label('')
                                                ->weight('bold')
                                                ->color(fn($record) => match ($record->status_to) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'completed' => 'info',
                                                    'pending' => 'warning',
                                                    default => 'gray',
                                                }),

                                            TextEntry::make('actionBy.name')
                                                ->label('Oleh')
                                                ->badge()
                                                ->color('gray'),

                                            TextEntry::make('action_at')
                                                ->label('Waktu')
                                                ->dateTime('d M Y H:i')
                                                ->since(),

                                            TextEntry::make('notes')
                                                ->label('Catatan')
                                                ->placeholder('Tidak ada catatan')
                                                ->columnSpanFull()
                                                ->visible(fn($record) => !empty($record->notes)),
                                        ])
                                        ->columns(3)
                                        ->contained(false),
                                ])
                                ->collapsible(),

                            Section::make('Lampiran')
                                ->schema([
                                    ImageEntry::make('attachments')
                                        ->label('')
                                        ->disk('public')
                                        ->height(150)
                                        ->width(200)
                                        ->square()
                                        ->stacked()
                                        ->limit(3)
                                        ->limitedRemainingText()
                                        ->placeholder('Tidak ada lampiran'),
                                ])
                                ->visible(fn ($record) => !empty($record->attachments)),
                        ])
                        ->columnSpan(2),

                    // SIDEBAR (1/3 width)
                    Group::make()
                        ->schema([
                            Section::make('Status & Approval')
                                ->schema([
                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->size('lg')
                                        ->color(fn ($record) => $record->status_color)
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'draft' => 'Draft',
                                            'pending' => 'Pending',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'completed' => 'Selesai',
                                            default => $state,
                                        }),

                                    TextEntry::make('approvedBy.name')
                                        ->label('Disetujui Oleh')
                                        ->placeholder('Belum disetujui')
                                        ->visible(fn ($record) => $record->approved_by),

                                    TextEntry::make('approved_at')
                                        ->label('Tanggal Approval')
                                        ->dateTime('d F Y H:i')
                                        ->visible(fn ($record) => $record->approved_at),

                                    TextEntry::make('catatan_approval')
                                        ->label('Catatan Approval')
                                        ->placeholder('Tidak ada catatan')
                                        ->visible(fn ($record) => $record->catatan_approval),
                                ]),

                            Section::make('Budget & Project')
                                ->schema([
                                    TextEntry::make('budgetAllocation.category_name')
                                        ->label('Kategori Budget')
                                        ->placeholder('Tidak terkait budget'),

                                    TextEntry::make('project.nama_project')
                                        ->label('Project Terkait')
                                        ->placeholder('Tidak terkait project'),
                                ]),

                            Section::make('Pembayaran')
                                ->schema([
                                    TextEntry::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
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

                                    TextEntry::make('nomor_referensi')
                                        ->label('Nomor Referensi')
                                        ->placeholder('Tidak ada')
                                        ->copyable(),
                                ]),

                            Section::make('Audit Trail')
                                ->schema([
                                    TextEntry::make('createdBy.name')
                                        ->label('Dibuat Oleh'),

                                    TextEntry::make('created_at')
                                        ->label('Dibuat Pada')
                                        ->dateTime('d F Y H:i'),

                                    TextEntry::make('updated_at')
                                        ->label('Diperbarui')
                                        ->dateTime('d F Y H:i')
                                        ->since(),
                                ]),
                        ])
                        ->columnSpan(1),
                ]),
            ]);
    }
}