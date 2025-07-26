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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Wizard;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;

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
                // Main Grid Layout (3 columns for large screens)
                Forms\Components\Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        // LEFT COLUMN (wider)
                        Forms\Components\Group::make()
                            ->schema([
                                // Informasi Transaksi
                                Section::make('Informasi Transaksi')
                                    ->icon('heroicon-o-document-text')
                                    ->description('Detail dasar transaksi')
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('nama_transaksi')
                                            ->label('Nama Transaksi')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Pembelian Alat Tulis Kantor')
                                            ->columnSpanFull(),

                                        Textarea::make('deskripsi')
                                            ->label('Deskripsi')
                                            ->maxLength(65535)
                                            ->rows(3)
                                            ->placeholder('Jelaskan detail transaksi ini...')
                                            ->columnSpanFull(),

                                        // Hidden field untuk total amount
                                        TextInput::make('total_amount')
                                            ->hidden()
                                            ->default(0),
                                    ]),

                                // Detail Items - Main Content
                                Section::make('Detail Items')
                                    ->icon('heroicon-o-shopping-cart')
                                    ->description('Daftar barang/jasa dalam transaksi')
                                    ->schema([
                                        Repeater::make('items')
                                            ->relationship()
                                            ->schema([
                                                // Gunakan Grid dengan 12 kolom untuk fleksibilitas
                                                Forms\Components\Grid::make(12)
                                                    ->schema([
                                                        // Baris 1: Nama Item (Lebar penuh)
                                                        TextInput::make('nama_item')
                                                            ->label('Nama Item')
                                                            ->required()
                                                            ->placeholder('Contoh: Kertas A4 80gsm')
                                                            ->columnSpan(12),

                                                        // Baris 2: Qty, Satuan, dan Harga
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
                                                            })
                                                            ->columnSpan([
                                                                'default' => 12, // Tumpuk di layar terkecil
                                                                'sm' => 2,       // Ambil 2 dari 12 kolom di layar kecil
                                                            ]),

                                                        TextInput::make('satuan')
                                                            ->label('Satuan')
                                                            ->placeholder('pcs')
                                                            ->default('pcs')
                                                            ->columnSpan([
                                                                'default' => 12,
                                                                'sm' => 3,       // Ambil 3 dari 12 kolom
                                                            ]),

                                                        TextInput::make('harga_satuan')
                                                            ->label('Harga')
                                                            ->numeric()
                                                            ->required()
                                                            ->minValue(0)
                                                            ->prefix('Rp')
                                                            ->placeholder('0')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                $qty = $get('kuantitas') ?? 1;
                                                                $set('subtotal', $qty * $state);
                                                            })
                                                            ->columnSpan([
                                                                'default' => 12,
                                                                'sm' => 7,       // Ambil 7 kolom sisanya, jadi lebih lebar
                                                            ]),

                                                        // Baris 3: Subtotal (Lebar penuh)
                                                        Forms\Components\Placeholder::make('subtotal_display')
                                                            ->label('Subtotal')
                                                            ->content(function (Forms\Get $get) {
                                                                $qty = $get('kuantitas') ?? 0;
                                                                $harga = $get('harga_satuan') ?? 0;
                                                                $subtotal = $qty * $harga;
                                                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                                            })
                                                            ->columnSpan(12),

                                                        // Baris 4: Deskripsi (Lebar penuh)
                                                        Textarea::make('deskripsi_item')
                                                            ->label('Deskripsi/Spesifikasi')
                                                            ->rows(2)
                                                            ->placeholder('Detail spesifikasi, merek, warna, ukuran...')
                                                            ->columnSpan(12),
                                                    ]),
                                            ])
                                            ->addActionLabel('➕ Tambah Item')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(function (array $state): ?string {
                                                if (!empty($state['nama_item'])) {
                                                    $qty = $state['kuantitas'] ?? 0;
                                                    $harga = $state['harga_satuan'] ?? 0;
                                                    $subtotal = $qty * $harga;
                                                    return $state['nama_item'] . ' - Rp ' . number_format($subtotal, 0, ',', '.');
                                                }
                                                return 'Item Baru';
                                            })
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->maxItems(50)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $total = 0;
                                                if (is_array($state)) {
                                                    foreach ($state as $item) {
                                                        $qty = $item['kuantitas'] ?? 0;
                                                        $harga = $item['harga_satuan'] ?? 0;
                                                        $total += $qty * $harga;
                                                    }
                                                }
                                                $set('total_amount', $total);
                                            }),
                                    ]),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        // RIGHT COLUMN
                        Forms\Components\Group::make()
                            ->schema([
                                // Section for Main Details (Jenis, Tanggal, Status)
                                Section::make('Detail Utama')
                                    ->icon('heroicon-o-arrow-path')
                                    ->schema([
                                        Select::make('jenis_transaksi')
                                            ->label('Jenis Transaksi')
                                            ->options([
                                                'pemasukan' => 'Pemasukan',
                                                'pengeluaran' => 'Pengeluaran'
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn(callable $set) => $set('status', 'draft'))
                                            ->native(false),

                                        DatePicker::make('tanggal_transaksi')
                                            ->label('Tanggal')
                                            ->required()
                                            ->default(now())
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ]),

                                Forms\Components\Section::make('Alokasi Budget & Project')
                                    ->schema([
                                        Forms\Components\Select::make('budget_allocation_id')
                                            ->label('Alokasi Budget')
                                            ->relationship('budgetAllocation', 'id')
                                            ->getOptionLabelFromRecordUsing(
                                                fn($record) =>
                                                $record->category_name . ' - Rp ' . number_format($record->remaining_amount, 0, ',', '.')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                            ->helperText('Pilih alokasi budget yang akan digunakan'),

                                        Forms\Components\Select::make('project_id')
                                            ->label('Project Terkait')
                                            ->relationship('project', 'nama_project')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Opsional - jika terkait project tertentu'),

                                        Forms\Components\TextInput::make('nomor_referensi')
                                            ->label('Nomor Referensi')
                                            ->maxLength(255)
                                            ->placeholder('No. invoice, kwitansi, dll'),
                                    ]),

                                // Summary Card
                                Section::make('Ringkasan Transaksi')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Forms\Components\Placeholder::make('transaction_summary')
                                            ->label('')
                                            ->content(function (Forms\Get $get) {
                                                $items = $get('items') ?? [];
                                                $total = 0;
                                                $itemCount = 0;
                                                $totalQty = 0;

                                                foreach ($items as $item) {
                                                    if (!empty($item['nama_item'])) {
                                                        $qty = $item['kuantitas'] ?? 0;
                                                        $harga = $item['harga_satuan'] ?? 0;
                                                        $total += $qty * $harga;
                                                        $itemCount++;
                                                        $totalQty += $qty;
                                                    }
                                                }

                                                $jenis = $get('jenis_transaksi');
                                                $bgColor = $jenis === 'pemasukan' ? 'success' : 'primary';
                                                $icon = $jenis === 'pemasukan' ? '💰' : '💸';
                                                $prefix = $jenis === 'pemasukan' ? '+' : '-';

                                                return new \Illuminate\Support\HtmlString("
                                                    <div class='bg-{$bgColor}-50 dark:bg-{$bgColor}-900/20 p-4 rounded-lg border border-{$bgColor}-200 dark:border-{$bgColor}-700'>
                                                        <div class='text-center mb-3'>
                                                            <div class='text-2xl mb-1'>{$icon}</div>
                                                            <div class='text-xs font-medium text-{$bgColor}-700 dark:text-{$bgColor}-300 uppercase tracking-wide'>
                                                                " . ($jenis === 'pemasukan' ? 'Total Pemasukan' : 'Total Pengeluaran') . "
                                                            </div>
                                                            <div class='text-xl font-bold text-{$bgColor}-600 dark:text-{$bgColor}-400 mt-1'>
                                                                {$prefix} Rp " . number_format($total, 0, ',', '.') . "
                                                            </div>
                                                        </div>
                                                        
                                                        <div class='grid grid-cols-2 gap-3 pt-3 border-t border-{$bgColor}-200 dark:border-{$bgColor}-700'>
                                                            <div class='text-center'>
                                                                <div class='text-lg font-semibold text-{$bgColor}-600 dark:text-{$bgColor}-400'>{$itemCount}</div>
                                                                <div class='text-xs text-{$bgColor}-700 dark:text-{$bgColor}-300'>Items</div>
                                                            </div>
                                                            <div class='text-center'>
                                                                <div class='text-lg font-semibold text-{$bgColor}-600 dark:text-{$bgColor}-400'>{$totalQty}</div>
                                                                <div class='text-xs text-{$bgColor}-700 dark:text-{$bgColor}-300'>Quantity</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ");
                                            }),
                                    ]),

                                // Payment Info - Conditional for Pengeluaran
                                Section::make('💳 Info Pembayaran')
                                    ->description('Metode dan referensi pembayaran')
                                    ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Select::make('metode_pembayaran')
                                            ->label('Metode Pembayaran')
                                            ->options([
                                                'cash' => '💵 Tunai',
                                                'transfer' => '🏦 Transfer Bank',
                                                'debit' => '💳 Kartu Debit',
                                                'credit' => '💳 Kartu Kredit',
                                                'e_wallet' => '📱 E-Wallet',
                                                'cek' => '📄 Cek',
                                            ])
                                            ->placeholder('Pilih metode...')
                                            ->native(false),

                                        TextInput::make('nomor_referensi')
                                            ->label('No. Referensi')
                                            ->placeholder('INV-001, PO-123')
                                            ->helperText('Invoice, PO, atau referensi lain'),
                                    ]),

                                // Attachments
                                Section::make('📎 Lampiran')
                                    ->description('Upload dokumen pendukung')
                                    ->collapsible()
                                    ->collapsed(fn(Forms\Get $get) => empty($get('attachments')))
                                    ->schema([
                                        Repeater::make('attachments')
                                            ->label(false)
                                            ->addActionLabel('📎 Tambah File')
                                            ->schema([
                                                FileUpload::make('filename')
                                                    ->label('File')
                                                    ->disk('public')
                                                    ->directory('transaksi-attachments')
                                                    ->preserveFilenames()
                                                    ->maxSize(5120) // 5MB
                                                    ->acceptedFileTypes([
                                                        'application/pdf',
                                                        'image/jpeg',
                                                        'image/png',
                                                        'image/jpg',
                                                        'image/webp'
                                                    ])
                                                    ->imageEditor()
                                                    ->imageEditorAspectRatios([
                                                        '16:9',
                                                        '4:3',
                                                        '1:1',
                                                    ])
                                                    ->panelLayout('grid')
                                                    ->required()
                                                    ->columnSpanFull(),

                                                Forms\Components\Grid::make([
                                                    'default' => 1,
                                                    'sm' => 2,
                                                ])
                                                    ->schema([
                                                        Select::make('type')
                                                            ->label('Jenis')
                                                            ->options([
                                                                'bukti_pembayaran' => '🧾 Bukti Bayar',
                                                                'invoice' => '📋 Invoice',
                                                                'kwitansi' => '🧾 Kwitansi',
                                                                'nota' => '📄 Nota',
                                                                'po' => '📝 PO',
                                                                'kontrak' => '📑 Kontrak',
                                                                'lainnya' => '📎 Lainnya'
                                                            ])
                                                            ->default('bukti_pembayaran')
                                                            ->required()
                                                            ->native(false),

                                                        TextInput::make('uploaded_by')
                                                            ->label('Upload oleh')
                                                            ->default(fn() => auth()->user()->name)
                                                            ->disabled()
                                                            ->dehydrated(),
                                                    ]),

                                                Textarea::make('description')
                                                    ->label('Keterangan')
                                                    ->rows(2)
                                                    ->placeholder('Deskripsi dokumen...')
                                                    ->columnSpanFull(),

                                                // Hidden metadata
                                                TextInput::make('uploaded_at')
                                                    ->hidden()
                                                    ->default(fn() => now()->toIso8601String()),
                                            ])
                                            ->itemLabel(function (array $state): ?string {
                                                if (isset($state['type']) && isset($state['filename'])) {
                                                    $typeLabels = [
                                                        'bukti_pembayaran' => '🧾',
                                                        'invoice' => '📋',
                                                        'kwitansi' => '🧾',
                                                        'nota' => '📄',
                                                        'po' => '📝',
                                                        'kontrak' => '📑',
                                                        'lainnya' => '📎'
                                                    ];
                                                    $icon = $typeLabels[$state['type']] ?? '📎';
                                                    $filename = is_string($state['filename']) ? basename($state['filename']) : 'File baru';
                                                    return $icon . ' ' . $filename;
                                                }
                                                return '📎 Lampiran Baru';
                                            })
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->collapsible(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('nomor_transaksi')->searchable()->sortable(),
                BadgeColumn::make('jenis_transaksi')->colors(['success' => 'pemasukan', 'danger' => 'pengeluaran']),
                TextColumn::make('nama_transaksi')->searchable(),
                TextColumn::make('tanggal_transaksi')->date('d M Y')->sortable(),
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
                        auth()->user()->hasRole(['admin', 'direktur', 'keuangan']))
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

            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending' &&
                        auth()->user()->hasRole(['admin', 'keuangan', 'direktur']))
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
                            ->body('Transaksi telah disetujui dan menunggu pembayaran.')
                            ->success()
                            ->send();
                    }),

                // Action untuk Reject
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending' &&
                        auth()->user()->hasRole(['admin', 'keuangan', 'direktur']))
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
                    ->visible(fn($record) => auth()->user()->hasRole(['admin', 'direktur', 'keuangan']) &&
                        in_array($record->status, ['draft', 'pending'])),
            ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole(['admin']))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

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