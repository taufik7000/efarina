<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;
use App\Models\PengajuanAnggaran;
use App\Models\Project;
use App\Models\BudgetCategory;
use App\Models\BudgetSubcategory;
use App\Models\BudgetAllocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PengajuanAnggaranResource extends Resource
{
    protected static ?string $model = PengajuanAnggaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';
    protected static ?int $navigationSort = 2;

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

                        Forms\Components\Select::make('project_id')
                            ->label('Project Terkait')
                            ->relationship(
                                name: 'project',
                                titleAttribute: 'nama_project',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = auth()->user();

                                    if (!$user) {
                                        return $query->whereRaw('1 = 0');
                                    }

                                    // Admin dan Redaksi bisa pilih semua project
                                    if ($user->hasRole(['admin', 'redaksi'])) {
                                        return $query;
                                    }

                                    // Team hanya bisa pilih project yang mereka terlibat
                                    return $query->where(function ($q) use ($user) {
                                        $q->where('created_by', $user->id)
                                            ->orWhere('project_manager_id', $user->id)
                                            ->orWhereJsonContains('team_members', (string) $user->id);
                                    });
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih project (opsional)')
                            ->helperText('Kosongkan jika pengajuan tidak terkait project spesifik'),

                        Forms\Components\DatePicker::make('tanggal_dibutuhkan')
                            ->label('Tanggal Dibutuhkan')
                            ->required()
                            ->native(false)
                            ->minDate(now()),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Kebutuhan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Jelaskan secara detail kebutuhan anggaran ini'),

                        Forms\Components\Textarea::make('justifikasi')
                            ->label('Justifikasi Bisnis')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Jelaskan mengapa anggaran ini penting untuk bisnis'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Item Anggaran')
                    ->schema([
                        Forms\Components\Repeater::make('detail_items')
                            ->label('Item Anggaran')
                            ->schema([
                                // Budget Selection
                                Forms\Components\Select::make('budget_category_id')
                                    ->label('Kategori Budget')
                                    ->options(function () {
                                        return BudgetCategory::active()
                                            ->whereHas('allocations', function ($q) {
                                                $q->whereHas('budgetPlan', fn($query) => $query->where('status', 'active'))
                                                    ->whereRaw('allocated_amount > used_amount');
                                            })
                                            ->pluck('nama_kategori', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpan(2)
                                    ->afterStateUpdated(fn(callable $set) => $set('budget_subcategory_id', null)),

                                Forms\Components\Select::make('budget_subcategory_id')
                                    ->label('Sub Kategori')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('budget_category_id');
                                        if (!$categoryId)
                                            return [];

                                        return BudgetSubcategory::whereHas('allocations', function ($q) {
                                            $q->whereHas('budgetPlan', fn($query) => $query->where('status', 'active'))
                                                ->whereRaw('allocated_amount > used_amount');
                                        })
                                            ->where('budget_category_id', $categoryId)
                                            ->pluck('nama_subkategori', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpan(2)
                                    ->placeholder('Pilih kategori terlebih dahulu'),

                                // Item Details
                                Forms\Components\TextInput::make('item_name')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(3),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi Item')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpan(3),

                                // Quantity & Price
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->columnSpan(2)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $unitPrice = (float) ($get('unit_price') ?? 0);
                                        $qty = (float) ($state ?? 1);
                                        $total = $qty * $unitPrice;
                                        $set('total_price', $total);
                                        // Trigger total calculation
                                        $set('../../_trigger_calculation', now()->timestamp);
                                    }),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live()
                                    ->columnSpan(2)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $qty = (float) ($get('quantity') ?? 1);
                                        $unitPrice = (float) ($state ?? 0);
                                        $total = $qty * $unitPrice;
                                        $set('total_price', $total);
                                        // Trigger total calculation
                                        $set('../../_trigger_calculation', now()->timestamp);
                                    }),

                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->minItems(1)
                            ->addActionLabel('+ Tambah Item')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn(array $state): ?string => $state['item_name'] ?? 'Item Baru')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Trigger calculation when items added/removed
                                $set('_trigger_calculation', now()->timestamp);
                            }),

                        // Hidden field untuk trigger calculation
                        Forms\Components\Hidden::make('_trigger_calculation')
                            ->dehydrated(false) // Tidak disimpan ke database
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $items = $get('detail_items') ?? [];
                                $total = 0;

                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        $total += (float) ($item['total_price'] ?? 0);
                                    }
                                }

                                $set('total_anggaran', $total);
                            }),

                        // Total Anggaran (Auto calculated)
                        Forms\Components\TextInput::make('total_anggaran')
                            ->label('Total Anggaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->reactive()
                            ->afterStateHydrated(function ($component, callable $get, callable $set) {
                                // Auto calculate saat form load
                                $items = $get('detail_items') ?? [];
                                $total = 0;

                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        $total += (float) ($item['total_price'] ?? 0);
                                    }
                                }

                                $component->state($total);
                                $set('_trigger_calculation', now()->timestamp);
                            }),
                    ]),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('workflow_status')
                            ->label('Status Workflow')
                            ->content(function ($record) {
                                if (!$record)
                                    return '📝 Draft - Belum diajukan';

                                return match ($record->status) {
                                    'draft' => '📝 Draft - Belum diajukan',
                                    'pending_redaksi' => '👥 Menunggu approval redaksi',
                                    'pending_keuangan' => '💰 Menunggu approval keuangan/direktur',
                                    'approved' => '✅ Disetujui - Siap digunakan',
                                    'rejected' => '❌ Ditolak',
                                    default => '❓ Status tidak dikenal'
                                };
                            }),

                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Catatan Redaksi')
                            ->disabled()
                            ->rows(2)
                            ->visible(fn($record) => $record && $record->redaksi_notes),

                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Catatan Keuangan/Direktur')
                            ->disabled()
                            ->rows(2)
                            ->visible(fn($record) => $record && $record->keuangan_notes),
                    ])
                    ->columns(1)
                    ->visible(fn($context) => $context === 'edit' || $context === 'view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('judul_pengajuan')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->judul_pengajuan;
                    }),

                Tables\Columns\TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->searchable()
                    ->limit(20)
                    ->placeholder('Tidak terkait project')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_anggaran')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending_redaksi',
                        'info' => 'pending_keuangan',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Draft',
                        'pending_redaksi' => 'Pending Redaksi',
                        'pending_keuangan' => 'Pending Keuangan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state)
                    }),

                Tables\Columns\TextColumn::make('tanggal_dibutuhkan')
                    ->label('Tgl Dibutuhkan')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn($record) => $record->tanggal_dibutuhkan->isPast() ? 'danger' : 'primary'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),

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
                        'pending_redaksi' => 'Pending Redaksi',
                        'pending_keuangan' => 'Pending Keuangan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'nama_project')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('tanggal_dibutuhkan')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('tanggal_dibutuhkan', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('tanggal_dibutuhkan', '<=', $data['until']));
                    }),

                Tables\Filters\Filter::make('urgent')
                    ->label('Urgent (< 7 hari)')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->where('tanggal_dibutuhkan', '<=', now()->addDays(7))
                    ),

                Tables\Filters\Filter::make('my_requests')
                    ->label('Pengajuan Saya')
                    ->query(fn(Builder $query): Builder => $query->where('created_by', auth()->id()))
                    ->default(),
            ])
            ->actions([
                // Submit untuk approval
                Tables\Actions\Action::make('submit')
                    ->label('Ajukan ke Redaksi')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        $record->status === 'draft' &&
                        $record->created_by === auth()->id()
                    )
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

                // Redaksi Actions
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('redaksi_approve')
                        ->label('Setujui (Redaksi)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status === 'pending_redaksi' &&
                            auth()->check() && auth()->user()->hasRole(['redaksi', 'admin'])
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

                    Tables\Actions\Action::make('redaksi_reject')
                        ->label('Tolak (Redaksi)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            $record->status === 'pending_redaksi' &&
                            auth()->check() && auth()->user()->hasRole(['redaksi', 'admin'])
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
                ])
                    ->label('Redaksi')
                    ->color('warning')
                    ->visible(
                        fn($record) =>
                        $record->status === 'pending_redaksi' &&
                        auth()->check() && auth()->user()->hasRole(['redaksi', 'admin'])
                    ),

                // Keuangan/Direktur Actions
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('keuangan_approve')
                        ->label('Setujui (Final)')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status === 'pending_keuangan' &&
                            auth()->check() && auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
                        )
                        ->form([
                            Forms\Components\Textarea::make('keuangan_notes')
                                ->label('Catatan Keuangan')
                                ->placeholder('Tambahkan catatan (opsional)')
                                ->rows(3),
                        ])
                        ->action(function (PengajuanAnggaran $record, array $data): void {
                            // Create transaksi when approved
                            DB::transaction(function () use ($record, $data) {
                                $record->update([
                                    'status' => 'approved',
                                    'keuangan_approved_by' => auth()->id(),
                                    'keuangan_approved_at' => now(),
                                    'keuangan_notes' => $data['keuangan_notes'] ?? null,
                                ]);

                                // Update budget allocations
                                foreach ($record->detail_items as $item) {
                                    $allocation = BudgetAllocation::where('budget_subcategory_id', $item['budget_subcategory_id'])
                                        ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
                                        ->first();

                                    if ($allocation) {
                                        $allocation->increment('used_amount', $item['total_price']);
                                    }
                                }
                            });

                            Notification::make()
                                ->title('Pengajuan Final Approved!')
                                ->body("Pengajuan '{$record->judul_pengajuan}' telah disetujui dan budget dialokasikan.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('keuangan_reject')
                        ->label('Tolak (Final)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            $record->status === 'pending_keuangan' &&
                            auth()->check() && auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
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
                ])
                    ->label('Keuangan')
                    ->color('info')
                    ->visible(
                        fn($record) =>
                        $record->status === 'pending_keuangan' &&
                        auth()->check() && auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
                    ),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(
                        fn($record) =>
                        $record->status === 'draft' &&
                        $record->created_by === auth()->id()
                    ),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (PengajuanAnggaran $record): void {
                        $newRecord = $record->replicate([
                            'nomor_pengajuan',
                            'status',
                            'redaksi_approved_by',
                            'redaksi_approved_at',
                            'redaksi_notes',
                            'keuangan_approved_by',
                            'keuangan_approved_at',
                            'keuangan_notes',
                        ]);

                        $newRecord->judul_pengajuan = $record->judul_pengajuan . ' (Copy)';
                        $newRecord->status = 'draft';
                        $newRecord->save();

                        Notification::make()
                            ->title('Pengajuan Diduplikat')
                            ->body('Pengajuan berhasil diduplikat sebagai draft baru.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->check() && auth()->user()->hasRole(['admin']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto refresh every 30 seconds
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

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

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

        if (!$user) {
            return null;
        }

        if ($user->hasRole(['redaksi'])) {
            return static::getModel()::where('status', 'pending_redaksi')->count() ?: null;
        }

        if ($user->hasRole(['keuangan', 'direktur'])) {
            return static::getModel()::where('status', 'pending_keuangan')->count() ?: null;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}