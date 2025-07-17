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
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_transaksi')
                            ->label('Nomor Transaksi')
                            ->disabled()
                            ->placeholder('Otomatis diisi sistem')
                            ->dehydrated(false),

                        Forms\Components\Select::make('jenis_transaksi')
                            ->label('Jenis Transaksi')
                            ->options([
                                'pemasukan' => 'Pemasukan',
                                'pengeluaran' => 'Pengeluaran',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('budget_allocation_id', null)),

                        Forms\Components\DatePicker::make('tanggal_transaksi')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\TextInput::make('nama_transaksi')
                            ->label('Nama Transaksi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Pembayaran Listrik Januari'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Menunggu Approval',
                                'approved' => 'Menunggu Pembayaran',
                                'rejected' => 'Ditolak',
                                'completed' => 'Selesai',
                            ])
                            ->required()
                            ->default('draft'),

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
                            ->searchable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alokasi Budget & Project')
                    ->schema([
                        Forms\Components\Select::make('budget_allocation_id')
                            ->label('Alokasi Budget')
                            ->relationship('budgetAllocation', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->category_name . ' - Rp ' . number_format($record->remaining_amount, 0, ',', '.')
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail & Keterangan')
                    ->schema([
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Lampiran')
                            ->multiple()
                            ->directory('transaksi-attachments')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->helperText('Upload foto, PDF, atau dokumen pendukung'),
                    ]),

                Forms\Components\Section::make('Detail Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Item Transaksi')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('nama_item')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('kuantitas')
                                    ->label('Kuantitas')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $harga = $get('harga_satuan') ?? 0;
                                        $set('subtotal', $state * $harga);
                                    }),

                                Forms\Components\TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->placeholder('pcs, kg, buah')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $qty = $get('kuantitas') ?? 1;
                                        $set('subtotal', $qty * $state);
                                    }),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('deskripsi_item')
                                    ->label('Keterangan Item')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->addActionLabel('Tambah Item')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->defaultItems(1),
                    ]),

                Forms\Components\Section::make('Informasi Approval')
                    ->schema([
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tanggal Approval')
                            ->disabled(),

                        Forms\Components\Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->disabled(),

                        Forms\Components\Textarea::make('catatan_approval')
                            ->label('Catatan Approval')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && ($record->approved_at || $record->approved_by)),
            ]);
    }

    public static function getTabs(): array
    {
        return [
            // Tab "Semua" tidak perlu lagi karena ini adalah default view
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_transaksi')
                    ->label('Nama Transaksi')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($record) => $record->jenis_transaksi === 'pemasukan' ? 'success' : 'danger')
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

                Tables\Columns\TextColumn::make('budgetAllocation.category_name')
                    ->label('Kategori Budget')
                    ->limit(25)
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->limit(20)
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Tidak ada'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Menunggu Pembayaran',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'e_wallet' => 'E-Wallet',
                        'cek' => 'Cek',
                    ]),

                Tables\Filters\Filter::make('tanggal_transaksi')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn ($q) => $q->whereDate('tanggal_transaksi', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn ($q) => $q->whereDate('tanggal_transaksi', '<=', $data['sampai_tanggal']));
                    }),

                Tables\Filters\Filter::make('pending_approval')
                    ->label('Perlu Approval')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending')),

                Tables\Filters\Filter::make('draft')
                    ->label('Draft')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'draft')),

                Tables\Filters\Filter::make('approved')
                    ->label('Menunggu Pembayaran')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'approved')),

                Tables\Filters\Filter::make('completed')
                    ->label('Selesai')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed')),

                Tables\Filters\Filter::make('rejected')
                    ->label('Ditolak')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'rejected')),
            ])
            ->actions([
                // Action untuk Mark as Paid (dari approved ke completed)
                Tables\Actions\Action::make('mark_paid')
                    ->label('Tandai Terbayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved' && 
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
                        
                        Notification::make()
                            ->title('Pembayaran Dikonfirmasi')
                            ->body("Transaksi {$record->nomor_transaksi} telah ditandai sebagai terbayar")
                            ->success()
                            ->send();
                    }),

                // Action untuk Approve
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending' && 
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
                    ->visible(fn ($record) => $record->status === 'pending' && 
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
                    ->visible(fn ($record) => auth()->user()->hasRole(['admin', 'super-admin', 'keuangan']) &&
                             in_array($record->status, ['draft', 'pending'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin']))
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

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }
        
        // Badge untuk direktur/admin - pending approval
        if ($user->hasRole(['admin', 'super-admin', 'direktur'])) {
            $count = static::getModel()::where('status', 'pending')->count();
            return $count > 0 ? (string) $count : null;
        }
        
        // Badge untuk keuangan - approved (menunggu pembayaran)
        if ($user->hasRole(['keuangan'])) {
            $count = static::getModel()::where('status', 'approved')->count();
            return $count > 0 ? (string) $count : null;
        }
        
        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}