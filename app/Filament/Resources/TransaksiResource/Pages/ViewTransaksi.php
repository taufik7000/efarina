<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ViewEntry; 
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
class ViewTransaksi extends ViewRecord
{
    protected static string $resource = TransaksiResource::class;

protected function getHeaderActions(): array
{
    $actions = [];

    // Edit Action - for draft and pending
    if (auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']) &&
        in_array($this->record->status, ['draft', 'pending'])) {
        $actions[] = Actions\EditAction::make();
    }

    // Approve Action - for pending status
    if ($this->record->status === 'pending' &&
        auth()->user()->hasRole(['admin', 'keuangan', 'direktur'])) {
        $actions[] = Actions\Action::make('approve')
            ->label('✅ Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->form([
                Forms\Components\Textarea::make('catatan_approval')
                    ->label('Catatan Approval')
                    ->placeholder('Tambahkan catatan persetujuan (opsional)')
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $this->record->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                    'catatan_approval' => $data['catatan_approval'] ?? null,
                ]);

                Notification::make()
                    ->title('Transaksi berhasil disetujui')
                    ->success()
                    ->send();

                return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
            })
            ->requiresConfirmation()
            ->modalHeading('Setujui Transaksi')
            ->modalDescription('Apakah Anda yakin ingin menyetujui transaksi ini?');
    }

    // Reject Action - for pending status
    if ($this->record->status === 'pending' &&
        auth()->user()->hasRole(['admin', 'keuangan', 'direktur'])) {
        $actions[] = Actions\Action::make('reject')
            ->label('❌ Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('catatan_approval')
                    ->label('Alasan Penolakan')
                    ->placeholder('Jelaskan alasan penolakan transaksi ini')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $this->record->update([
                    'status' => 'rejected',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                    'catatan_approval' => $data['catatan_approval'],
                ]);

                Notification::make()
                    ->title('Transaksi ditolak')
                    ->warning()
                    ->send();

                return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
            })
            ->requiresConfirmation()
            ->modalHeading('Tolak Transaksi')
            ->modalDescription('Apakah Anda yakin ingin menolak transaksi ini?');
    }

    // Submit for Approval - for draft status
    if ($this->record->status === 'draft' &&
        auth()->user()->hasRole(['admin', 'super-admin', 'keuangan'])) {
        $actions[] = Actions\Action::make('submit_approval')
            ->label('📤 Submit untuk Approval')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->action(function () {
                $this->record->update([
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Transaksi berhasil disubmit untuk approval')
                    ->success()
                    ->send();

                return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
            })
            ->requiresConfirmation()
            ->modalHeading('Submit untuk Approval')
            ->modalDescription('Transaksi akan dikirim ke Admin/Direktur untuk disetujui.');
    }

    // Mark as Paid - for approved status
    if ($this->record->status === 'approved' &&
        auth()->user()->hasRole(['admin', 'super-admin', 'keuangan'])) {
        $actions[] = Actions\Action::make('mark_paid')
            ->label('💳 Tandai Terbayar')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->form([
                Forms\Components\Select::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => '💵 Tunai',
                        'transfer' => '🏦 Transfer Bank',
                        'debit' => '💳 Kartu Debit',
                        'credit' => '💳 Kartu Kredit',
                        'e_wallet' => '📱 E-Wallet',
                        'cek' => '📄 Cek',
                    ])
                    ->required()
                    ->native(false),
                    
                Forms\Components\TextInput::make('nomor_referensi')
                    ->label('Nomor Referensi')
                    ->placeholder('No. transaksi, no. cek, dll'),
                    
                Forms\Components\FileUpload::make('bukti_transfer')
                    ->label('Bukti Transfer/Pembayaran')
                    ->image()
                    ->directory('bukti-pembayaran')
                    ->imageEditor()
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->helperText('Upload bukti pembayaran (Max: 5MB)')
                    ->required(),
                    
                Forms\Components\Textarea::make('catatan_pembayaran')
                    ->label('Catatan Pembayaran')
                    ->placeholder('Tambahkan catatan pembayaran jika diperlukan')
                    ->rows(3),
            ])
            ->action(function (array $data) {
                // Handle file upload and update record
                $attachments = $this->record->attachments ?? [];

                if (isset($data['bukti_transfer'])) {
                    $attachments[] = [
                        'type' => 'bukti_pembayaran',
                        'filename' => $data['bukti_transfer'],
                        'uploaded_by' => auth()->user()->name,
                        'uploaded_at' => now()->toISOString(),
                        'description' => 'Bukti pembayaran - ' . $data['metode_pembayaran'],
                    ];
                }

                $this->record->update([
                    'status' => 'completed',
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'nomor_referensi' => $data['nomor_referensi'] ?? null,
                    'attachments' => $attachments,
                    'catatan_approval' => ($this->record->catatan_approval ?? '') .
                        "\n\n=== PEMBAYARAN DIKONFIRMASI ===\n" .
                        "Tanggal: " . now()->format('d M Y H:i') . "\n" .
                        "Metode: " . $data['metode_pembayaran'] . "\n" .
                        "No. Referensi: " . ($data['nomor_referensi'] ?? '-') . "\n" .
                        "Dikonfirmasi oleh: " . auth()->user()->name . "\n" .
                        "Catatan: " . ($data['catatan_pembayaran'] ?? 'Tidak ada catatan'),
                ]);

                Notification::make()
                    ->title('Pembayaran berhasil dikonfirmasi')
                    ->success()
                    ->send();

                return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
            })
            ->modalHeading('Konfirmasi Pembayaran')
            ->modalDescription('Tandai transaksi sebagai terbayar dan upload bukti pembayaran.');
    }

    // Reopen/Reset - for rejected status (optional)
    if ($this->record->status === 'rejected' &&
        auth()->user()->hasRole(['admin', 'super-admin'])) {
        $actions[] = Actions\Action::make('reopen')
            ->label('🔄 Buka Kembali')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->action(function () {
                $this->record->update([
                    'status' => 'draft',
                    'approved_at' => null,
                    'approved_by' => null,
                ]);

                Notification::make()
                    ->title('Transaksi dibuka kembali untuk diedit')
                    ->success()
                    ->send();

                return redirect()->to($this->getResource()::getUrl('view', ['record' => $this->record]));
            })
            ->requiresConfirmation()
            ->modalHeading('Buka Kembali Transaksi')
            ->modalDescription('Transaksi akan dikembalikan ke status draft untuk diedit ulang.');
    }

    return $actions;
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
                                    TextEntry::make('nomor_transaksi')->label('Nomor Transaksi')->weight('bold')->copyable(),
                                    TextEntry::make('nama_transaksi')->label('Nama Transaksi')->weight('bold'),
                                    TextEntry::make('tanggal_transaksi')->label('Tanggal Transaksi')->date('d F Y'),
                                    TextEntry::make('jenis_transaksi')->label('Jenis Transaksi')->badge()->color(fn($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger')->formatStateUsing(fn(string $state): string => match ($state) { 'pemasukan' => 'Pemasukan', 'pengeluaran' => 'Pengeluaran', default => $state }),
                                    TextEntry::make('total_amount')->label('Total Amount')->money('IDR')->size('lg')->weight('bold')->color(fn($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger'),
                                    TextEntry::make('status')->label('Status')->badge()->color(fn(string $state) => match ($state) { 'draft' => 'gray', 'pending' => 'warning', 'approved' => 'info', 'completed' => 'success', 'rejected' => 'danger', default => 'gray' })->formatStateUsing(fn(string $state): string => match ($state) { 'draft' => 'Draft', 'pending' => 'Menunggu Approval', 'approved' => 'Menunggu Pembayaran', 'rejected' => 'Ditolak', 'completed' => 'Selesai', default => $state }),
                                    TextEntry::make('deskripsi')->label('Deskripsi')->columnSpanFull()->placeholder('Tidak ada deskripsi'),
                                ])->columns(2),

                            Section::make('Detail Items')
                                ->schema([
                                    RepeatableEntry::make('items')->label('')->schema([
                                        TextEntry::make('nama_item')->label('Nama Item')->weight('bold'),
                                        TextEntry::make('kuantitas')->label('Qty')->suffix(fn($record) => ' ' . ($record->satuan ?? 'pcs')),
                                        TextEntry::make('harga_satuan')->label('Harga Satuan')->money('IDR'),
                                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR')->weight('bold'),
                                        TextEntry::make('deskripsi_item')->label('Keterangan')->placeholder('Tidak ada keterangan')->columnSpanFull()->visible(fn($record) => !empty($record->deskripsi_item)),
                                    ])->columns(4)->contained(false),
                                ])->visible(fn($record) => $record->items->count() > 0),

                            // V V V BAGIAN LAMPIRAN DIGANTI DENGAN BLADE VIEW V V V
                            Section::make('Lampiran & Bukti Pembayaran')
                                ->schema([
                                    ViewEntry::make('attachments')
                                        ->label('') // Sembunyikan label bawaan
                                        ->view('filament.infolists.components.attachments-viewer')
                                ]),
                            // ^ ^ ^ AKHIR DARI BAGIAN LAMPIRAN ^ ^ ^

                            Section::make('Catatan Approval')
                                ->schema([
                                    TextEntry::make('catatan_approval')->label('')->formatStateUsing(fn(?string $state): string => $state ? nl2br(e($state)) : 'Tidak ada catatan')->html(),
                                ])
                                ->visible(fn($record) => !empty($record->catatan_approval)),
                        ])
                        ->columnSpan(2),

                    // SIDEBAR (1/3 width)
                    Group::make()
                        ->schema([

Section::make('Status & Approval')->schema([
    // Status - Always visible
    TextEntry::make('status')
        ->label('Status')
        ->badge()
        ->color(fn (string $state): string => match ($state) {
            'draft' => 'gray',
            'pending' => 'warning', 
            'approved' => 'primary',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        })
        ->formatStateUsing(fn (string $state): string => match ($state) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Approval',
            'approved' => 'Menunggu Pembayaran', 
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            default => ucfirst($state),
        }),

    // Approval info - Only if approved/rejected/completed
    TextEntry::make('approved_at')
        ->label('Tanggal Approval')
        ->dateTime('d F Y H:i')
        ->visible(fn($record) => in_array($record->status, ['approved', 'completed', 'rejected'])),
    
    TextEntry::make('approvedBy.name')
        ->label('Disetujui Oleh')
        ->badge()
        ->color('success')
        ->visible(fn($record) => in_array($record->status, ['approved', 'completed', 'rejected'])),

    // Approval notes - Only if exists
    TextEntry::make('catatan_approval')
        ->label('Catatan')
        ->visible(fn($record) => !empty($record->catatan_approval)),
    ]),
                            Section::make('Budget & Project')->schema([
                                TextEntry::make('budgetAllocation.category_name')->label('Kategori Budget')->placeholder('Tidak terkait budget'),
                                TextEntry::make('project.nama_project')->label('Project Terkait')->placeholder('Tidak terkait project'),
                                TextEntry::make('pengajuanAnggaran.nomor_pengajuan')->label('No. Pengajuan Anggaran')->placeholder('Tidak dari pengajuan')->visible(fn($record) => $record->pengajuan_anggaran_id),
                            ]),
                            Section::make('Pembayaran')->schema([
                                TextEntry::make('metode_pembayaran')->label('Metode Pembayaran')->badge()->color('info')
                                    ->formatStateUsing(fn($state): string => match (is_array($state) ? 'array' : $state) { 'cash' => 'Tunai', 'transfer' => 'Transfer', 'debit' => 'Debit', 'credit' => 'Credit', 'e_wallet' => 'E-Wallet', 'cek' => 'Cek', 'array' => implode(', ', $state), default => $state ?? 'Tidak ada' }),
                                TextEntry::make('nomor_referensi')->label('Nomor Referensi')->placeholder('Tidak ada')->copyable(),
                            ]),
                            Section::make('Audit Trail')
                                ->collapsible()
                                ->schema([
                                    ViewEntry::make('audit_trail')
                                        ->label('') // Sembunyikan label
                                        ->view('filament.infolists.components.audit-trail-viewer')
                                ]),
                        ])
                        ->columnSpan(1),
                ]),
            ]);
    }
}