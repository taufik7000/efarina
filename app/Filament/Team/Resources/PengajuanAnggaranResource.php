<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;
use App\Models\PengajuanAnggaran;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PengajuanAnggaranResource extends Resource
{
    protected static ?string $model = PengajuanAnggaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\TextInput::make('judul_pengajuan')
                            ->label('Judul Pengajuan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('project_id')
                            ->label('Project Terkait')
                            ->relationship('project', 'nama_project')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih project (opsional)')
                            ->helperText('Kosongkan jika pengajuan tidak terkait project spesifik'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('justifikasi')
                            ->label('Justifikasi')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan mengapa anggaran ini diperlukan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Anggaran')
                    ->schema([
                        Forms\Components\TextInput::make('total_anggaran')
                            ->label('Total Anggaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('sisa_anggaran', $state);
                            }),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'marketing' => 'Marketing',
                                'operasional' => 'Operasional',
                                'equipment' => 'Equipment',
                                'travel' => 'Travel & Accommodation',
                                'training' => 'Training & Development',
                                'software' => 'Software & Tools',
                                'other' => 'Lainnya',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_dibutuhkan')
                            ->label('Tanggal Dibutuhkan')
                            ->required()
                            ->native(false),

                        Forms\Components\Repeater::make('detail_items')
                            ->label('Detail Item')
                            ->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->minItems(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('workflow_status')
                            ->label('Status Workflow')
                            ->content(function ($record) {
                                if (!$record) return '📝 Draft - Belum diajukan';
                                
                                $status = match($record->status) {
                                    'draft' => '📝 Draft - Belum diajukan',
                                    'pending_redaksi' => '👥 Menunggu approval redaksi',
                                    'pending_keuangan' => '💰 Menunggu approval keuangan/direktur',
                                    'approved' => '✅ Disetujui - Siap digunakan',
                                    'rejected' => '❌ Ditolak',
                                    default => '❓ Status tidak dikenal'
                                };
                                
                                return $status;
                            }),

                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Catatan Redaksi')
                            ->disabled()
                            ->rows(2)
                            ->visible(fn ($record) => $record && $record->redaksi_notes),

                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Catatan Keuangan/Direktur')
                            ->disabled()
                            ->rows(2)
                            ->visible(fn ($record) => $record && $record->keuangan_notes),
                    ])
                    ->columns(1)
                    ->visible(fn ($context) => $context === 'edit' || $context === 'view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('judul_pengajuan')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->searchable()
                    ->limit(20)
                    ->placeholder('Tidak terkait project'),

                Tables\Columns\TextColumn::make('total_anggaran')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending_redaksi',
                        'info' => 'pending_keuangan',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Draft',
                        'pending_redaksi' => 'Pending Redaksi',
                        'pending_keuangan' => 'Pending Keuangan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_redaksi' => 'Pending Redaksi',
                        'pending_keuangan' => 'Pending Keuangan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'nama_project'),
            ])
            ->actions([
                // Submit untuk approval
                Tables\Actions\Action::make('submit')
                    ->label('Ajukan ke Redaksi')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft' && $record->created_by === auth()->id())
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan ke Redaksi')
                    ->modalDescription('Setelah diajukan, pengajuan tidak bisa diubah dan akan masuk ke workflow approval.')
                    ->action(function (PengajuanAnggaran $record): void {
                        $record->update([
                            'status' => 'pending_redaksi',
                            'tanggal_pengajuan' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Pengajuan Terkirim')
                            ->body("Pengajuan '{$record->judul_pengajuan}' telah dikirim ke redaksi untuk review.")
                            ->success()
                            ->send();
                    }),

                // Redaksi Approval
                Tables\Actions\Action::make('redaksi_approve')
                    ->label('Setujui (Redaksi)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === 'pending_redaksi' && 
                        auth()->user()->hasRole(['redaksi', 'admin'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Catatan Redaksi')
                            ->placeholder('Tambahkan catatan (opsional)')
                            ->rows(3),
                    ])
                    ->action(function (PengajuanAnggaran $record, array $data): void {
                        $record->update([
                            'status' => 'pending_keuangan',
                            'redaksi_approved_by' => auth()->id(),
                            'redaksi_approved_at' => now(),
                            'redaksi_notes' => $data['redaksi_notes'] ?? null,
                        ]);
                        
                        Notification::make()
                            ->title('Pengajuan Disetujui Redaksi')
                            ->body("Pengajuan diteruskan ke keuangan/direktur untuk final approval.")
                            ->success()
                            ->send();
                    }),

                // Redaksi Reject
                Tables\Actions\Action::make('redaksi_reject')
                    ->label('Tolak (Redaksi)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === 'pending_redaksi' && 
                        auth()->user()->hasRole(['redaksi', 'admin'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PengajuanAnggaran $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'redaksi_approved_by' => auth()->id(),
                            'redaksi_approved_at' => now(),
                            'redaksi_notes' => $data['redaksi_notes'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->body("Pengajuan ditolak oleh redaksi.")
                            ->warning()
                            ->send();
                    }),

                // Keuangan/Direktur Final Approval
                Tables\Actions\Action::make('keuangan_approve')
                    ->label('Setujui (Final)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === 'pending_keuangan' && 
                        auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Catatan Keuangan')
                            ->placeholder('Tambahkan catatan (opsional)')
                            ->rows(3),
                    ])
                    ->action(function (PengajuanAnggaran $record, array $data): void {
                        $record->update([
                            'status' => 'approved',
                            'keuangan_approved_by' => auth()->id(),
                            'keuangan_approved_at' => now(),
                            'keuangan_notes' => $data['keuangan_notes'] ?? null,
                        ]);
                        
                        Notification::make()
                            ->title('Pengajuan Final Approved!')
                            ->body("Pengajuan '{$record->judul_pengajuan}' telah disetujui dan siap digunakan.")
                            ->success()
                            ->send();
                    }),

                // Keuangan/Direktur Reject
                Tables\Actions\Action::make('keuangan_reject')
                    ->label('Tolak (Final)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === 'pending_keuangan' && 
                        auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PengajuanAnggaran $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'keuangan_approved_by' => auth()->id(),
                            'keuangan_approved_at' => now(),
                            'keuangan_notes' => $data['keuangan_notes'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->body("Pengajuan ditolak oleh keuangan/direktur.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'draft' && $record->created_by === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin'])),
                ]),
            ]);
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

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Admin bisa lihat semua
        if ($user->hasRole(['admin'])) {
            return parent::getEloquentQuery();
        }

        // Redaksi bisa lihat yang pending redaksi + semua yang sudah lewat redaksi
        if ($user->hasRole(['redaksi'])) {
            return parent::getEloquentQuery()
                ->where(function ($query) {
                    $query->where('status', 'pending_redaksi')
                          ->orWhereIn('status', ['pending_keuangan', 'approved', 'rejected']);
                });
        }

        // Keuangan/Direktur bisa lihat yang pending keuangan + approved/rejected
        if ($user->hasRole(['keuangan', 'direktur'])) {
            return parent::getEloquentQuery()
                ->whereIn('status', ['pending_keuangan', 'approved', 'rejected']);
        }

        // Team hanya bisa lihat pengajuan mereka sendiri
        return parent::getEloquentQuery()
            ->where('created_by', $user->id);
    }

public static function getNavigationBadge(): ?string
{
    $user = auth()->user();
    
    // Return null jika user tidak login
    if (!$user) {
        return null;
    }
    
    if ($user->hasRole(['redaksi', 'admin'])) {
        return static::getModel()::where('status', 'pending_redaksi')->count();
    }
    
    if ($user->hasRole(['keuangan', 'direktur'])) {
        return static::getModel()::where('status', 'pending_keuangan')->count();
    }
    
    return null;
}
}