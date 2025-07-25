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
                ->visible(fn() => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']) &&
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
                                        ->color(fn($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger')
                                        ->formatStateUsing(fn(string $state): string => match ($state) {
                                            'pemasukan' => 'Pemasukan',
                                            'pengeluaran' => 'Pengeluaran',
                                            default => $state,
                                        }),

                                    TextEntry::make('total_amount')
                                        ->label('Total Amount')
                                        ->money('IDR')
                                        ->size('lg')
                                        ->weight('bold')
                                        ->color(fn($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger'),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn(string $state) => match ($state) {
                                            'draft' => 'gray',
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn(string $state): string => match ($state) {
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
                                                ->suffix(fn($record) => ' ' . ($record->satuan ?? 'pcs')),

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
                                                ->visible(fn($record) => !empty($record->deskripsi_item)),
                                        ])
                                        ->columns(4)
                                        ->contained(false),
                                ])
                                ->visible(fn($record) => $record->items->count() > 0),

                            // Lampiran dengan handling untuk array dan string
                            Section::make('Lampiran & Bukti Pembayaran')
                                ->schema([
                                    // RepeatableEntry untuk attachments array
                                    RepeatableEntry::make('attachments')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('type')
                                                ->label('Jenis')
                                                ->badge()
                                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                                    'bukti_pembayaran' => 'Bukti Pembayaran',
                                                    'invoice' => 'Invoice',
                                                    'receipt' => 'Kwitansi',
                                                    default => 'Lampiran',
                                                })
                                                ->color(fn(?string $state): string => match ($state) {
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
                                                ->formatStateUsing(
                                                    fn(?string $state): string =>
                                                    $state ? \Carbon\Carbon::parse($state)->format('d M Y H:i') : 'Tidak diketahui'
                                                ),

                                            // PERBAIKAN UTAMA: ImageEntry dengan path URL yang benar
                                            ImageEntry::make('filename')
                                                ->label('Preview')
                                                ->height(250)
                                                ->width(400)
                                                ->extraAttributes(['class' => 'rounded-lg shadow-sm border'])
                                                ->getStateUsing(function ($record) {
                                                    // PERBAIKAN: Gunakan URL langsung tanpa 'storage/'
                                                    if (!isset($record['filename']))
                                                        return null;

                                                    $filename = $record['filename'];
                                                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                    if ($isImage) {
                                                        // URL langsung ke file: /bukti-pembayaran/filename.jpg
                                                        return asset($filename);
                                                    }

                                                    return null;
                                                })
                                                ->visible(function ($record) {
                                                    if (!isset($record['filename']))
                                                        return false;

                                                    $filename = $record['filename'];
                                                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                                                    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                }),

                                            // Link untuk melihat gambar di tab baru
                                            TextEntry::make('filename')
                                                ->label('Lihat Gambar')
                                                ->formatStateUsing(function ($record): string {
                                                    if (!isset($record['filename']))
                                                        return 'File tidak tersedia';

                                                    $filename = $record['filename'];
                                                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                    if ($isImage) {
                                                        return '🖼️ Buka gambar di tab baru';
                                                    } else {
                                                        return '📎 ' . basename($filename);
                                                    }
                                                })
                                                ->url(function ($record) {
                                                    if (!isset($record['filename']))
                                                        return null;

                                                    // PERBAIKAN: URL langsung tanpa 'storage/'
                                                    return asset($record['filename']);
                                                })
                                                ->openUrlInNewTab()
                                                ->color('primary'),

                                            // Link download
                                            TextEntry::make('filename')
                                                ->label('Download')
                                                ->formatStateUsing(function ($record): string {
                                                    $originalName = $record['original_name'] ?? $record['filename'] ?? 'file';
                                                    return '⬇️ Download: ' . basename($originalName);
                                                })
                                                ->url(function ($record) {
                                                    if (!isset($record['filename']))
                                                        return null;

                                                    // PERBAIKAN: URL langsung tanpa 'storage/'
                                                    return asset($record['filename']);
                                                })
                                                ->openUrlInNewTab()
                                                ->color('success'),

                                            // Debug info (opsional, bisa dihapus setelah berhasil)
                                            TextEntry::make('debug_url')
                                                ->label('Debug URL')
                                                ->formatStateUsing(function ($record): string {
                                                    if (!isset($record['filename']))
                                                        return 'No filename';

                                                    $filename = $record['filename'];
                                                    $assetUrl = asset($filename);
                                                    $storageUrl = asset('storage/' . $filename);

                                                    return "Asset URL: {$assetUrl}\n" .
                                                        "Storage URL: {$storageUrl}\n" .
                                                        "Filename: {$filename}";
                                                })
                                                ->visible(config('app.debug'))
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->contained(false)
                                        ->visible(
                                            fn($record) =>
                                            !empty($record->attachments) &&
                                            is_array($record->attachments) &&
                                            count($record->attachments) > 0
                                        ),

                                    // Pesan jika tidak ada lampiran
                                    TextEntry::make('no_attachments')
                                        ->label('')
                                        ->formatStateUsing(fn(): string => '📎 Tidak ada lampiran')
                                        ->color('gray')
                                        ->visible(
                                            fn($record) =>
                                            empty($record->attachments) ||
                                            (is_array($record->attachments) && count($record->attachments) === 0)
                                        ),
                                ])
                                ->collapsible(),

                            Section::make('Catatan Approval')
                                ->schema([
                                    TextEntry::make('catatan_approval')
                                        ->label('')
                                        ->formatStateUsing(
                                            fn(?string $state): string =>
                                            $state ? nl2br(e($state)) : 'Tidak ada catatan'
                                        )
                                        ->html(),
                                ])
                                ->visible(fn($record) => !empty($record->catatan_approval)),
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
                                        ->visible(fn($record) => $record->approved_at),

                                    TextEntry::make('approvedBy.name')
                                        ->label('Disetujui Oleh')
                                        ->badge()
                                        ->color('success')
                                        ->visible(fn($record) => $record->approved_at),
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
                                        ->visible(fn($record) => $record->pengajuan_anggaran_id),
                                ]),

                            Section::make('Pembayaran')
                                ->schema([
                                    TextEntry::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->badge()
                                        ->color('info')
                                        ->formatStateUsing(fn(?string $state): string => match ($state) {
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