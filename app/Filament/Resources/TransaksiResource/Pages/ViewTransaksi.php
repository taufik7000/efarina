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
                                        ->color(fn ($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger')
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

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn (string $state) => match ($state) {
                                            'draft' => 'gray',
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'draft' => 'Draft',
                                            'pending' => 'Menunggu Approval',
                                            'approved' => 'Menunggu Pembayaran',
                                            'rejected' => 'Ditolak',
                                            'completed' => 'Selesai',
                                            default => $state,
                                        }),

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
                                                ->placeholder('Tidak ada keterangan')
                                                ->columnSpanFull()
                                                ->visible(fn ($record) => !empty($record->deskripsi_item)),
                                        ])
                                        ->columns(4)
                                        ->contained(false),
                                ])
                                ->visible(fn ($record) => $record->items->count() > 0),

                            // Lampiran dengan handling untuk array dan string
                            Section::make('Lampiran & Bukti Pembayaran')
                                ->schema([
                                    // Untuk attachments array (bukti pembayaran dari action)
                                    RepeatableEntry::make('attachments')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('type')
                                                ->label('Jenis')
                                                ->badge()
                                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                    'bukti_pembayaran' => 'Bukti Pembayaran',
                                                    'invoice' => 'Invoice',
                                                    'receipt' => 'Kwitansi',
                                                    default => 'Lampiran',
                                                })
                                                ->color(fn (?string $state): string => match ($state) {
                                                    'bukti_pembayaran' => 'success',
                                                    'invoice' => 'info',
                                                    'receipt' => 'warning',
                                                    default => 'gray',
                                                }),

                                            TextEntry::make('description')
                                                ->label('Deskripsi')
                                                ->placeholder('Tidak ada deskripsi'),

                                            TextEntry::make('uploaded_by')
                                                ->label('Diupload oleh')
                                                ->badge()
                                                ->color('gray'),

                                            TextEntry::make('uploaded_at')
                                                ->label('Tanggal Upload')
                                                ->formatStateUsing(fn (?string $state): string => 
                                                    $state ? \Carbon\Carbon::parse($state)->format('d M Y H:i') : 'Tidak diketahui'
                                                ),

                                            // Tampilkan gambar bukti pembayaran
                                            ImageEntry::make('filename')
                                                ->label('Preview')
                                                ->disk('public')
                                                ->height(200)
                                                ->width(300)
                                                ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                                                ->visible(fn ($record) => 
                                                    isset($record['filename']) && 
                                                    is_string($record['filename']) &&
                                                    in_array(strtolower(pathinfo($record['filename'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                                ),

                                            // Link download untuk file PDF atau non-image
                                            TextEntry::make('filename')
                                                ->label('Download File')
                                                ->formatStateUsing(fn (?string $state): string => 
                                                    $state ? '📎 ' . basename($state) : 'File tidak tersedia'
                                                )
                                                ->url(fn ($record) => 
                                                    isset($record['filename']) ? asset('storage/' . $record['filename']) : null
                                                )
                                                ->openUrlInNewTab()
                                                ->color('primary')
                                                ->visible(fn ($record) => 
                                                    isset($record['filename']) && 
                                                    is_string($record['filename']) &&
                                                    !in_array(strtolower(pathinfo($record['filename'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                                ),
                                        ])
                                        ->columns(2)
                                        ->contained(false)
                                        ->visible(fn ($record) => 
                                            !empty($record->attachments) && 
                                            is_array($record->attachments) && 
                                            count($record->attachments) > 0
                                        ),

                                    // Fallback untuk attachments string (legacy atau direct upload)
                                    ImageEntry::make('attachments')
                                        ->label('Lampiran')
                                        ->disk('public')
                                        ->height(200)
                                        ->width(300)
                                        ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                                        ->visible(fn ($record) => 
                                            !empty($record->attachments) && 
                                            is_string($record->attachments)
                                        ),

                                    // Pesan jika tidak ada lampiran
                                    TextEntry::make('no_attachments')
                                        ->label('')
                                        ->formatStateUsing(fn (): string => '📎 Tidak ada lampiran')
                                        ->color('gray')
                                        ->visible(fn ($record) => 
                                            empty($record->attachments) || 
                                            (is_array($record->attachments) && count($record->attachments) === 0)
                                        ),
                                ])
                                ->collapsible(),

                            Section::make('Catatan Approval')
                                ->schema([
                                    TextEntry::make('catatan_approval')
                                        ->label('')
                                        ->formatStateUsing(fn (?string $state): string => 
                                            $state ? nl2br(e($state)) : 'Tidak ada catatan'
                                        )
                                        ->html(),
                                ])
                                ->visible(fn ($record) => !empty($record->catatan_approval)),
                        ])
                        ->columnSpan(2),

                    // SIDEBAR (1/3 width)
                    Group::make()
                        ->schema([
                            Section::make('Status & Approval')
                                ->schema([
                                    TextEntry::make('approved_at')
                                        ->label('Tanggal Approval')
                                        ->dateTime('d F Y H:i')
                                        ->placeholder('Belum disetujui')
                                        ->visible(fn ($record) => $record->approved_at),

                                    TextEntry::make('approvedBy.name')
                                        ->label('Disetujui Oleh')
                                        ->badge()
                                        ->color('success')
                                        ->visible(fn ($record) => $record->approved_at),
                                ]),

                            Section::make('Budget & Project')
                                ->schema([
                                    TextEntry::make('budgetAllocation.category_name')
                                        ->label('Kategori Budget')
                                        ->placeholder('Tidak terkait budget'),

                                    TextEntry::make('project.nama_project')
                                        ->label('Project Terkait')
                                        ->placeholder('Tidak terkait project'),

                                    TextEntry::make('pengajuanAnggaran.nomor_pengajuan')
                                        ->label('No. Pengajuan Anggaran')
                                        ->placeholder('Tidak dari pengajuan')
                                        ->visible(fn ($record) => $record->pengajuan_anggaran_id),
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