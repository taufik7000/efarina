<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;
use App\Models\PengajuanAnggaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class PengajuanAnggaranResource extends Resource
{
    protected static ?string $model = PengajuanAnggaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\TextInput::make('judul_pengajuan')
                            ->label('Judul Pengajuan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'project' => 'Project',
                                'operasional' => 'Operasional',
                                'investasi' => 'Investasi',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->default('project'),

                        Forms\Components\DatePicker::make('tanggal_dibutuhkan')
                            ->label('Tanggal Dibutuhkan')
                            ->required()
                            ->minDate(now()),

                        Forms\Components\Textarea::make('justifikasi')
                            ->label('Justifikasi')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan alasan mengapa anggaran ini diperlukan')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Anggaran')
                    ->schema([
                        Forms\Components\Repeater::make('detail_items')
                            ->label('Detail Item Anggaran')
                            ->schema([
                                Forms\Components\TextInput::make('nama_item')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

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
                                    ->placeholder('pcs, kg, buah, hari')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $qty = $get('kuantitas') ?? 1;
                                        $set('subtotal', $state * $qty);
                                    }),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('deskripsi_item')
                                    ->label('Deskripsi Item')
                                    ->rows(2)
                                    ->columnSpan(2),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['nama_item'] ?? 'Item baru')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $total = collect($state)->sum('subtotal');
                                $set('total_anggaran', $total);
                            }),

                        Forms\Components\TextInput::make('total_anggaran')
                            ->label('Total Anggaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Total akan dihitung otomatis dari detail items'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Pengajuan')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('redaksi_approval_status')
                            ->label('Status Redaksi')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('keuangan_approval_status')
                            ->label('Status Keuangan')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Catatan Redaksi')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->visible(fn ($record) => $record && $record->redaksi_notes),

                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Catatan Keuangan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->visible(fn ($record) => $record && $record->keuangan_notes),

                        Forms\Components\Placeholder::make('workflow_info')
                            ->label('Informasi Workflow')
                            ->content(function ($record) {
                                if (!$record) return '⏳ Setelah pengajuan dibuat, akan masuk ke workflow approval redaksi → keuangan.';
                                
                                $content = '';
                                
                                // Status workflow
                                if ($record->status === 'draft') {
                                    $content .= "📝 Status: Draft - Siap untuk diajukan\n";
                                } elseif ($record->status === 'pending') {
                                    $content .= "⏳ Status: Sedang dalam proses approval\n";
                                } elseif ($record->status === 'approved') {
                                    $content .= "✅ Status: Disetujui - Siap digunakan\n";
                                } elseif ($record->status === 'rejected') {
                                    $content .= "❌ Status: Ditolak\n";
                                }
                                
                                // Redaksi Status
                                $redaksiStatus = match($record->redaksi_approval_status) {
                                    'pending' => '⏳ Menunggu approval redaksi',
                                    'approved' => '✅ Disetujui redaksi pada ' . $record->redaksi_approved_at?->format('d M Y H:i'),
                                    'rejected' => '❌ Ditolak redaksi pada ' . $record->redaksi_approved_at?->format('d M Y H:i'),
                                };
                                
                                // Keuangan Status
                                $keuanganStatus = match($record->keuangan_approval_status) {
                                    'pending' => '⏳ Menunggu approval keuangan',
                                    'approved' => '✅ Disetujui keuangan pada ' . $record->keuangan_approved_at?->format('d M Y H:i'),
                                    'rejected' => '❌ Ditolak keuangan pada ' . $record->keuangan_approved_at?->format('d M Y H:i'),
                                };
                                
                                $content .= "\nRedaksi: {$redaksiStatus}\n";
                                $content .= "Keuangan: {$keuanganStatus}";
                                
                                return $content;
                            })
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('judul_pengajuan')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn ($record) => $record->kategori_color),

                Tables\Columns\TextColumn::make('total_anggaran')
                    ->label('Total Anggaran')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

                Tables\Columns\TextColumn::make('redaksi_approval_status')
                    ->label('Redaksi')
                    ->badge()
                    ->color(fn ($record) => $record->redaksi_status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                Tables\Columns\TextColumn::make('keuangan_approval_status')
                    ->label('Keuangan')
                    ->badge()
                    ->color(fn ($record) => $record->keuangan_status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                Tables\Columns\IconColumn::make('is_used')
                    ->label('Digunakan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_dibutuhkan')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->tanggal_dibutuhkan->isPast() ? 'danger' : null),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'project' => 'Project',
                        'operasional' => 'Operasional',
                        'investasi' => 'Investasi',
                        'lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\SelectFilter::make('redaksi_approval_status')
                    ->label('Status Redaksi')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('keuangan_approval_status')
                    ->label('Status Keuangan')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\TernaryFilter::make('is_used')
                    ->label('Sudah Digunakan')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Digunakan')
                    ->falseLabel('Belum Digunakan'),

                Tables\Filters\Filter::make('my_pengajuan')
                    ->label('Pengajuan Saya')
                    ->query(fn (Builder $query): Builder => $query->where('created_by', auth()->id()))
                    ->default(),

                Tables\Filters\Filter::make('available')
                    ->label('Tersedia Untuk Digunakan')
                    ->query(fn (Builder $query): Builder => 
                        $query->approved()
                              ->where('is_used', false)
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan Proposal Anggaran')
                    ->modalDescription('Setelah diajukan, proposal akan masuk ke workflow approval dan tidak bisa diubah lagi.')
                    ->action(function (PengajuanAnggaran $record): void {
                        $record->update(['status' => 'pending']);
                        
                        Notification::make()
                            ->title('Pengajuan Berhasil')
                            ->body("Pengajuan '{$record->judul_pengajuan}' telah diajukan dan akan diproses oleh redaksi.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (PengajuanAnggaran $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->nomor_pengajuan = null;
                        $newRecord->status = 'draft';
                        $newRecord->redaksi_approval_status = 'pending';
                        $newRecord->keuangan_approval_status = 'pending';
                        $newRecord->redaksi_approved_by = null;
                        $newRecord->redaksi_approved_at = null;
                        $newRecord->redaksi_notes = null;
                        $newRecord->keuangan_approved_by = null;
                        $newRecord->keuangan_approved_at = null;
                        $newRecord->keuangan_notes = null;
                        $newRecord->is_used = false;
                        $newRecord->judul_pengajuan = $record->judul_pengajuan . ' (Copy)';
                        $newRecord->save();
                        
                        Notification::make()
                            ->title('Berhasil Duplikat')
                            ->body("Pengajuan berhasil diduplikat. Silakan edit dan ajukan kembali.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPengajuanAnggarans::route('/'),
            'create' => Pages\CreatePengajuanAnggaran::route('/create'),
            'view' => Pages\ViewPengajuanAnggaran::route('/{record}'),
            'edit' => Pages\EditPengajuanAnggaran::route('/{record}/edit'),
        ];
    }
}