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
                Section::make('Informasi Utama')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_transaksi')->label('Nama Transaksi')->required()->maxLength(255)->columnSpanFull(),
                        Select::make('jenis_transaksi')->label('Jenis Transaksi')->options(['pemasukan' => 'Pemasukan', 'pengeluaran' => 'Pengeluaran'])->required()->live(),
                        DatePicker::make('tanggal_transaksi')->label('Tanggal Transaksi')->required()->default(now()),
                        
                        // V V V PERUBAIKAN ADA DI SINI V V V
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Menunggu Approval',
                                'approved' => 'Menunggu Pembayaran',
                                'rejected' => 'Ditolak',
                                'completed' => 'Selesai',
                            ])
                            ->default('approved') // <-- Diubah ke 'approved'
                            ->required(),
                        // ^ ^ ^ AKHIR DARI PERUBAIKAN ^ ^ ^

                        Textarea::make('deskripsi')->label('Deskripsi')->maxLength(65535)->columnSpanFull(),
                    ]),

                Section::make('Asosiasi Budget & Project')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('budget_allocation_id')
                            ->label('Alokasi Budget')
                            ->relationship('budgetAllocation', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                $record->category_name . ' - Rp ' . number_format($record->remaining_amount, 0, ',', '.')
                            )
                            ->searchable(['category_name'])
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('jenis_transaksi') === 'pengeluaran'),

                        Select::make('project_id')
                            ->label('Project Terkait')
                            ->relationship('project', 'nama_project')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Detail Items')->schema([
                    Repeater::make('items')->relationship()->schema([
                        TextInput::make('nama_item')->required(),
                        TextInput::make('kuantitas')->numeric()->required()->default(1),
                        TextInput::make('harga_satuan')->numeric()->required()->prefix('IDR'),
                    ])->columns(3)->addActionLabel('Tambah Item')->defaultItems(0),
                ]),

                Section::make('Upload Bukti Pembayaran')->collapsible()->schema([
                    Repeater::make('attachments')->label(false)->addActionLabel('Tambah Bukti Pembayaran')->schema([
                        FileUpload::make('filename')->label('File Bukti')->disk('public')->directory('transaksi-attachments')->required(),
                        Select::make('type')->label('Jenis Bukti')->options(['bukti_pembayaran' => 'Bukti Pembayaran', 'invoice' => 'Invoice', 'kwitansi' => 'Kwitansi', 'lainnya' => 'Lainnya'])->default('bukti_pembayaran')->required(),
                        Textarea::make('description')->label('Keterangan (Opsional)')->rows(2),
                        TextInput::make('uploaded_by')->hidden()->default(fn() => auth()->user()->name),
                        TextInput::make('uploaded_at')->hidden()->default(fn() => now()->toIso8601String()),
                    ])
                    ->columns(2)
                    ->itemLabel(function (array $state): ?string {
                        if (isset($state['filename']) && is_string($state['filename'])) { return basename($state['filename']); }
                        return 'Lampiran baru';
                    }),
                ]),
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
                ->formatStateUsing(fn (string $state): string => match ($state) {
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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