<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Transaksi;
use App\Models\BudgetAllocation;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\Notification;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Semua Transaksi';
    protected static ?string $pluralModelLabel = 'Transaksi';
    protected static ?int $navigationSort = 6;


public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Grid::make(2)
                ->schema([
                    // KOLOM KIRI
                    Forms\Components\Group::make()
                        ->schema([
                            Section::make('Informasi Utama')
                                ->schema([
                                    TextInput::make('nama_transaksi')
                                        ->label('Nama Transaksi')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Masukkan nama transaksi'),

                                    Select::make('jenis_transaksi')
                                        ->label('Jenis Transaksi')
                                        ->options([
                                            'pemasukan' => 'Pemasukan',
                                            'pengeluaran' => 'Pengeluaran'
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn(callable $set) => $set('status', 'draft')),

                                    DatePicker::make('tanggal_transaksi')
                                        ->label('Tanggal Transaksi')
                                        ->required()
                                        ->default(now()),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft' => 'Draft',
                                            'pending' => 'Menunggu Approval',
                                            'approved' => 'Menunggu Pembayaran',
                                            'rejected' => 'Ditolak',
                                            'completed' => 'Selesai',
                                        ])
                                        ->default('draft')
                                        ->required(),

                                    Textarea::make('deskripsi')
                                        ->label('Deskripsi')
                                        ->maxLength(65535)
                                        ->rows(4)
                                        ->placeholder('Deskripsikan transaksi ini secara detail...'),

                                    // Hidden field untuk total amount
                                    TextInput::make('total_amount')
                                        ->hidden()
                                        ->default(0),
                                ]),

                            // Asosiasi Budget & Project - HANYA untuk PENGELUARAN
                            Section::make('Budget & Project')
                                ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                ->schema([
                                    Select::make('budget_allocation_id')
                                        ->label('Alokasi Budget')
                                        ->relationship('budgetAllocation', 'id')
                                        ->getOptionLabelFromRecordUsing(
                                            fn($record) =>
                                            $record->category_name . ' - Rp ' . number_format($record->remaining_amount, 0, ',', '.')
                                        )
                                        ->searchable(['category_name'])
                                        ->preload()
                                        ->placeholder('Pilih alokasi budget (opsional)'),

                                    Select::make('project_id')
                                        ->label('Project Terkait')
                                        ->relationship('project', 'nama_project')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Pilih project terkait (opsional)'),
                                ]),

                            // Informasi Pembayaran - untuk PENGELUARAN
                            Section::make('Informasi Pembayaran')
                                ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                ->schema([
                                    Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'cash' => 'Tunai',
                                            'transfer' => 'Transfer Bank',
                                            'debit' => 'Kartu Debit',
                                            'credit' => 'Kartu Kredit',
                                            'e_wallet' => 'E-Wallet',
                                            'cek' => 'Cek',
                                        ])
                                        ->placeholder('Pilih metode pembayaran'),

                                    TextInput::make('nomor_referensi')
                                        ->label('Nomor Referensi')
                                        ->placeholder('No. invoice, PO, atau referensi lainnya')
                                        ->helperText('Nomor untuk tracking pembayaran'),
                                ]),
                        ])
                        ->columnSpan(1),

                    // KOLOM KANAN
                    Forms\Components\Group::make()
                        ->schema([
                            // Detail Items - WAJIB
                            Section::make('Detail Items Transaksi')
                                ->description('Minimal 1 item harus diisi')
                                ->schema([
                                    Repeater::make('items')
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('nama_item')
                                                ->label('Nama Item')
                                                ->required()
                                                ->placeholder('Masukkan nama item')
                                                ->columnSpan(2),

                                            TextInput::make('kuantitas')
                                                ->label('Qty')
                                                ->numeric()
                                                ->required()
                                                ->default(1)
                                                ->minValue(1)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    $harga = $get('harga_satuan') ?? 0;
                                                    $set('subtotal', $state * $harga);
                                                }),

                                            TextInput::make('harga_satuan')
                                                ->label('Harga Satuan')
                                                ->numeric()
                                                ->required()
                                                ->prefix('IDR')
                                                ->placeholder('0')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    $qty = $get('kuantitas') ?? 1;
                                                    $set('subtotal', $qty * $state);
                                                }),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal')
                                                ->numeric()
                                                ->prefix('IDR')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->placeholder('Auto calculated'),

                                            TextInput::make('satuan')
                                                ->label('Satuan')
                                                ->placeholder('pcs, kg, meter')
                                                ->default('pcs'),

                                            Textarea::make('deskripsi_item')
                                                ->label('Deskripsi Item')
                                                ->rows(2)
                                                ->placeholder('Detail spesifikasi item (opsional)')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(4)
                                        ->addActionLabel('+ Tambah Item')
                                        ->reorderableWithButtons()
                                        ->collapsible()
                                        ->itemLabel(fn(array $state): ?string => $state['nama_item'] ?? 'Item Baru')
                                        ->defaultItems(1)
                                        ->minItems(1)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Calculate total from all items
                                            $total = 0;
                                            if (is_array($state)) {
                                                foreach ($state as $item) {
                                                    $qty = $item['kuantitas'] ?? 0;
                                                    $harga = $item['harga_satuan'] ?? 0;
                                                    $total += $qty * $harga;
                                                }
                                            }
                                            $set('calculated_total', $total);
                                        }),

                                    // Total Summary
                                    Forms\Components\Placeholder::make('total_summary')
                                        ->label('')
                                        ->content(function (Forms\Get $get) {
                                            $items = $get('items') ?? [];
                                            $total = 0;
                                            $itemCount = 0;

                                            foreach ($items as $item) {
                                                if (!empty($item['nama_item'])) {
                                                    $qty = $item['kuantitas'] ?? 0;
                                                    $harga = $item['harga_satuan'] ?? 0;
                                                    $total += $qty * $harga;
                                                    $itemCount++;
                                                }
                                            }

                                            return new \Illuminate\Support\HtmlString('
                                                <div class="bg-primary-50 dark:bg-primary-900/20 p-4 rounded-lg border border-primary-200 dark:border-primary-700">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Total Items:</span>
                                                        <span class="text-sm font-semibold text-primary-900 dark:text-primary-100">' . $itemCount . ' item(s)</span>
                                                    </div>
                                                    <div class="flex justify-between items-center border-t border-primary-200 dark:border-primary-700 pt-2 mt-2">
                                                        <span class="text-lg font-bold text-primary-900 dark:text-primary-100">TOTAL:</span>
                                                        <span class="text-xl font-bold text-primary-600 dark:text-primary-400">Rp ' . number_format($total, 0, ',', '.') . '</span>
                                                    </div>
                                                </div>
                                            ');
                                        }),
                                ]),

                            // Upload Bukti Pembayaran - OPSIONAL
                            Section::make('Bukti Pembayaran (Opsional)')
                                ->description('Dapat ditambahkan sekarang atau nanti')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Repeater::make('attachments')
                                        ->label(false)
                                        ->addActionLabel('+ Tambah Bukti')
                                        ->schema([
                                            FileUpload::make('filename')
                                                ->label('File')
                                                ->disk('public')
                                                ->directory('transaksi-attachments')
                                                ->preserveFilenames()
                                                ->maxSize(5120)
                                                ->acceptedFileTypes([
                                                    'application/pdf',
                                                    'image/jpeg',
                                                    'image/png',
                                                    'image/jpg',
                                                    'image/webp'
                                                ])
                                                ->imageEditor()
                                                ->required(),

                                            Select::make('type')
                                                ->label('Jenis')
                                                ->options([
                                                    'bukti_pembayaran' => 'Bukti Pembayaran',
                                                    'invoice' => 'Invoice',
                                                    'kwitansi' => 'Kwitansi',
                                                    'nota' => 'Nota',
                                                    'lainnya' => 'Lainnya'
                                                ])
                                                ->default('bukti_pembayaran')
                                                ->required(),

                                            Textarea::make('description')
                                                ->label('Keterangan')
                                                ->rows(2)
                                                ->placeholder('Deskripsikan bukti ini')
                                                ->columnSpanFull(),

                                            // Hidden metadata
                                            TextInput::make('uploaded_by')
                                                ->hidden()
                                                ->default(fn() => auth()->user()->name),

                                            TextInput::make('uploaded_at')
                                                ->hidden()
                                                ->default(fn() => now()->toIso8601String()),
                                        ])
                                        ->columns(2)
                                        ->itemLabel(function (array $state): ?string {
                                            if (isset($state['filename']) && is_string($state['filename'])) {
                                                return basename($state['filename']);
                                            }
                                            return 'Lampiran baru';
                                        })
                                        ->defaultItems(0),
                                ]),
                        ])
                        ->columnSpan(1),
                ])
        ]);
}
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_transaksi')->searchable()->sortable(),
                TextColumn::make('nama_transaksi')->searchable(),
                TextColumn::make('tanggal_transaksi')->date('d M Y')->sortable(),
                BadgeColumn::make('jenis_transaksi')->colors(['success' => 'pemasukan', 'danger' => 'pengeluaran']),
                TextColumn::make('total_amount')->money('IDR')->sortable(),
                BadgeColumn::make('status')->colors(['secondary' => 'draft', 'warning' => 'pending', 'primary' => 'approved', 'success' => 'completed', 'danger' => 'rejected'])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Menunggu Pembayaran',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        default => $state
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Tandai Terbayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'approved' &&
                        auth()->user()->hasRole(['admin', 'super-admin', 'keuangan']))
                    ->form([
                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                                'debit' => 'Kartu Debit',
                                'credit' => 'Kartu Kredit',
                                'e_wallet' => 'E-Wallet',
                                'cek' => 'Cek',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('nomor_referensi')
                            ->label('Nomor Referensi')
                            ->placeholder('No. transaksi, no. cek, dll'),
                        Forms\Components\FileUpload::make('bukti_transfer')
                            ->label('Bukti Transfer/Pembayaran')
                            ->image()
                            ->directory('bukti-pembayaran')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->helperText('Upload foto bukti transfer atau dokumen pembayaran (Max: 5MB)')
                            ->required(),
                        Forms\Components\Textarea::make('catatan_pembayaran')
                            ->label('Catatan Pembayaran')
                            ->placeholder('Tambahkan catatan pembayaran jika diperlukan')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        // Simpan bukti transfer ke attachments
                        $attachments = $record->attachments ?? [];

                        // Debug: pastikan file tersimpan dengan benar
                        if (isset($data['bukti_transfer'])) {
                            $fileName = $data['bukti_transfer'];

                            // Tambahkan ke array attachments dengan metadata lengkap
                            $attachments[] = [
                                'type' => 'bukti_pembayaran',
                                'filename' => $fileName, // Ini sudah termasuk path dari directory
                                'original_name' => $fileName,
                                'uploaded_by' => auth()->user()->name,
                                'uploaded_at' => now()->toISOString(),
                                'description' => 'Bukti pembayaran - ' . $data['metode_pembayaran'],
                                'file_size' => null, // Bisa ditambahkan jika diperlukan
                                'mime_type' => null,  // Bisa ditambahkan jika diperlukan
                            ];
                        }

                        $record->update([
                            'status' => 'completed',
                            'metode_pembayaran' => $data['metode_pembayaran'],
                            'nomor_referensi' => $data['nomor_referensi'] ?? null,
                            'attachments' => $attachments,
                            'catatan_approval' => ($record->catatan_approval ?? '') .
                                "\n\n=== PEMBAYARAN DIKONFIRMASI ===\n" .
                                "Tanggal: " . now()->format('d M Y H:i') . "\n" .
                                "Metode: " . $data['metode_pembayaran'] . "\n" .
                                "No. Referensi: " . ($data['nomor_referensi'] ?? '-') . "\n" .
                                "Dikonfirmasi oleh: " . auth()->user()->name . "\n" .
                                "Catatan: " . ($data['catatan_pembayaran'] ?? 'Tidak ada catatan') . "\n" .
                                "Bukti transfer: " . ($data['bukti_transfer'] ? 'Tersedia' : 'Tidak ada'),
                        ]);
                    }),

                // Action untuk Approve
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending' &&
                        auth()->user()->hasRole(['admin', 'super-admin', 'direktur']))
                    ->form([
                        Forms\Components\Textarea::make('catatan_approval')
                            ->label('Catatan Approval')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'catatan_approval' => $data['catatan_approval'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Transaksi berhasil disetujui')
                            ->success()
                            ->send();
                    }),

                // Action untuk Reject
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending' &&
                        auth()->user()->hasRole(['admin', 'super-admin', 'direktur']))
                    ->form([
                        Forms\Components\Textarea::make('catatan_approval')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'catatan_approval' => $data['catatan_approval'],
                        ]);

                        Notification::make()
                            ->title('Transaksi ditolak')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => auth()->user()->hasRole(['admin', 'super-admin', 'keuangan']) &&
                        in_array($record->status, ['draft', 'pending'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole(['admin', 'super-admin']))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    // ... (Sisa kode Anda seperti getRelations() dan getPages() tetap sama)
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'view' => Pages\ViewTransaksi::route('/{record}'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}