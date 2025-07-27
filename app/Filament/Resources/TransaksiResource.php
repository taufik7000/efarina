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
use Illuminate\Support\HtmlString;

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
                Forms\Components\Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
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

                                        TextInput::make('total_amount')
                                            ->hidden()
                                            ->default(0),
                                    ]),

                                Section::make('Detail Items')
                                    ->icon('heroicon-o-shopping-cart')
                                    ->description('Daftar barang/jasa dalam transaksi')
                                    ->schema([
                                        Repeater::make('items')
                                            ->relationship()
                                            ->schema([
                                                Forms\Components\Grid::make(12)
                                                    ->schema([
                                                        TextInput::make('nama_item')
                                                            ->label('Nama Item')
                                                            ->required()
                                                            ->placeholder('Contoh: Kertas A4 80gsm')
                                                            ->columnSpan(12),

                                                        TextInput::make('kuantitas')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->required()
                                                            ->default(1)
                                                            ->minValue(1)
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                // DIUBAH: Bersihkan harga sebelum kalkulasi
                                                                $hargaString = $get('harga_satuan') ?? '0';
                                                                $harga = (int) str_replace('.', '', $hargaString);
                                                                $set('subtotal', (int) $state * $harga);
                                                            })
                                                            ->columnSpan(['default' => 12, 'sm' => 2]),

                                                        TextInput::make('satuan')
                                                            ->label('Satuan')
                                                            ->placeholder('pcs')
                                                            ->default('pcs')
                                                            ->columnSpan(['default' => 12, 'sm' => 3]),

                                                        TextInput::make('harga_satuan')
                                                            ->label('Harga')
                                                            ->required()
                                                            ->minValue(0)
                                                            ->prefix('Rp')
                                                            ->placeholder('0')
                                                            ->extraInputAttributes([
                                                                'oninput' => "
                                                                let value = this.value.replace(/[^0-9]/g, '');
                                                                if (value) { this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
                                                            "
                                                            ])
                                                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                                                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                $qty = (int) ($get('kuantitas') ?? 1);
                                                                // DIUBAH: Pastikan state harga adalah angka bersih
                                                                $harga = (int) str_replace('.', '', (string) $state);
                                                                $set('subtotal', $qty * $harga);
                                                            })
                                                            ->columnSpan(['default' => 12, 'sm' => 7]),

                                                        Forms\Components\Placeholder::make('subtotal_display')
                                                            ->label('Subtotal')
                                                            ->content(function (Forms\Get $get) {
                                                                // DIUBAH: Bersihkan harga sebelum kalkulasi
                                                                $qty = (int) ($get('kuantitas') ?? 0);
                                                                $hargaString = $get('harga_satuan') ?? '0';
                                                                $harga = (int) str_replace('.', '', $hargaString);
                                                                $subtotal = $qty * $harga;
                                                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                                            })
                                                            ->columnSpan(12),

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
                                                    // DIUBAH: Bersihkan harga sebelum kalkulasi
                                                    $qty = (int) ($state['kuantitas'] ?? 0);
                                                    $hargaString = $state['harga_satuan'] ?? '0';
                                                    $harga = (int) str_replace('.', '', $hargaString);
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
                                                        // DIUBAH: Bersihkan harga sebelum kalkulasi
                                                        $qty = (int) ($item['kuantitas'] ?? 0);
                                                        $hargaString = $item['harga_satuan'] ?? '0';
                                                        $harga = (int) str_replace('.', '', $hargaString);
                                                        $total += $qty * $harga;
                                                    }
                                                }
                                                $set('total_amount', $total);
                                            }),
                                    ]),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Section::make('Detail Utama')
                                    ->icon('heroicon-o-arrow-path')
                                    ->schema([
                                        Select::make('jenis_transaksi')
                                            ->label('Jenis Transaksi')
                                            ->options(['pemasukan' => 'Pemasukan', 'pengeluaran' => 'Pengeluaran'])
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
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Forms\Components\Select::make('budget_allocation_id')
                                            ->label('Alokasi Budget')
                                            ->relationship('budgetAllocation', 'id')
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->category_name . ' - Rp ' . number_format($record->remaining_amount, 0, ',', '.'))
                                            ->options(function () {
                                                return \App\Models\BudgetAllocation::with(['category', 'subcategory', 'budgetPlan'])
                                                    ->whereHas('budgetPlan', function ($query) {
                                                        $query->where('status', 'active'); // Hanya budget plan aktif
                                                    })
                                                    ->whereRaw('allocated_amount > used_amount') // Hanya yang masih ada sisa
                                                    ->get()
                                                    ->mapWithKeys(function ($allocation) {
                                                        return [
                                                            $allocation->id => $allocation->category_name . ' - Rp ' . number_format($allocation->remaining_amount, 0, ',', '.')
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                            ->helperText('Hanya menampilkan alokasi yang masih memiliki sisa budget'),

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
                                                        // DIUBAH: Bersihkan harga sebelum kalkulasi
                                                        $qty = (int) ($item['kuantitas'] ?? 0);
                                                        $hargaString = $item['harga_satuan'] ?? '0';
                                                        $harga = (int) str_replace('.', '', $hargaString);
                                                        $total += $qty * $harga;
                                                        $itemCount++;
                                                        $totalQty += $qty;
                                                    }
                                                }

                                                $jenis = $get('jenis_transaksi');
                                                $bgColor = $jenis === 'pemasukan' ? 'success' : 'primary';
                                                $icon = $jenis === 'pemasukan' ? '💰' : '💸';
                                                $prefix = $jenis === 'pemasukan' ? '+' : '-';

                                                return new HtmlString(
                                                    "
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
                                                    </div>"
                                                );
                                            }),
                                    ]),

                                Section::make('Info Pembayaran')
                                    ->icon('heroicon-o-banknotes')
                                    ->description('Metode dan referensi pembayaran')
                                    ->visible(fn(Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Select::make('metode_pembayaran')
                                            ->label('Metode Pembayaran')
                                            ->options(['cash' => '💵 Tunai', 'transfer' => '🏦 Transfer Bank', 'debit' => '💳 Kartu Debit', 'credit' => '💳 Kartu Kredit', 'e_wallet' => '📱 E-Wallet', 'cek' => '📄 Cek'])
                                            ->placeholder('Pilih metode...')
                                            ->native(false),

                                        TextInput::make('nomor_referensi')
                                            ->label('No. Referensi')
                                            ->placeholder('INV-001, PO-123')
                                            ->helperText('Invoice, PO, atau referensi lain'),
                                    ]),

                                Section::make('Lampiran')
                                    ->icon('heroicon-o-paper-clip')
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
                                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                                    ->imageEditor()
                                                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                                    ->panelLayout('grid')
                                                    ->required()
                                                    ->columnSpanFull(),

                                                Forms\Components\Grid::make(['default' => 1, 'sm' => 2])
                                                    ->schema([
                                                        Select::make('type')
                                                            ->label('Jenis')
                                                            ->options(['bukti_pembayaran' => '🧾 Bukti Bayar', 'invoice' => '📋 Invoice', 'kwitansi' => '🧾 Kwitansi', 'nota' => '📄 Nota', 'po' => '📝 PO', 'kontrak' => '📑 Kontrak', 'lainnya' => '📎 Lainnya'])
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

                                                TextInput::make('uploaded_at')
                                                    ->hidden()
                                                    ->default(fn() => now()->toIso8601String()),
                                            ])
                                            ->itemLabel(function (array $state): ?string {
                                                if (isset($state['type']) && isset($state['filename'])) {
                                                    $typeLabels = ['bukti_pembayaran' => '🧾', 'invoice' => '📋', 'kwitansi' => '🧾', 'nota' => '📄', 'po' => '📝', 'kontrak' => '📑', 'lainnya' => '📎'];
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
        // Kode untuk table tidak perlu diubah, jadi saya persingkat untuk kejelasan
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // ... actions lainnya
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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