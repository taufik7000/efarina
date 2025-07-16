<?php
// app/Filament/Resources/TransaksiResource.php

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
                                'approved' => 'Disetujui',
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
                            ->placeholder('No. Invoice, Kwitansi, dll'),
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

                Tables\Columns\TextColumn::make('budgetAllocation.category_name')
                    ->label('Kategori Budget')
                    ->limit(25)
                    ->placeholder('Tidak ada'),

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
                        default => $state ?? '-',
                    }),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ]),

                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer Bank',
                        'debit' => 'Kartu Debit',
                        'credit' => 'Kartu Kredit',
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
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn ($q) => $q->whereDate('tanggal_transaksi', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn ($q) => $q->whereDate('tanggal_transaksi', '<=', $data['sampai_tanggal']));
                    }),

                Tables\Filters\Filter::make('pending_approval')
                    ->label('Perlu Approval')
                    ->query(fn ($query) => $query->where('status', 'pending')),
            ])
            ->actions([
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
                        $record->approve(auth()->id(), $data['catatan_approval'] ?? null);
                        Notification::make()
                            ->title('Transaksi berhasil disetujui')
                            ->success()
                            ->send();
                    }),

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
                        $record->reject(auth()->id(), $data['catatan_approval']);
                        Notification::make()
                            ->title('Transaksi ditolak')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'approved' && 
                             auth()->user()->hasRole(['admin', 'super-admin', 'keuangan']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->complete();
                        Notification::make()
                            ->title('Transaksi diselesaikan & budget allocation terupdate')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan']) &&
                             in_array($record->status, ['draft', 'pending'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => auth()->user()->hasRole(['admin', 'super-admin', 'direktur']) &&
                             $record->status === 'draft'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
                ]),
            ])
            ->defaultSort('tanggal_transaksi', 'desc')
            ->groups([
                Tables\Grouping\Group::make('jenis_transaksi')
                    ->label('Jenis Transaksi'),
                Tables\Grouping\Group::make('status')
                    ->label('Status'),
                Tables\Grouping\Group::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date(),
            ]);
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