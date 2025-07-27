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
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';

 public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Grid::make(2)
                ->schema([
                    // KOLOM KIRI
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make('Informasi Pengajuan')
                                ->schema([
                                    Forms\Components\TextInput::make('judul_pengajuan')
                                        ->label('Judul Pengajuan')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Masukkan judul pengajuan anggaran'),

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
                                                
                                                // Filter: Exclude completed & cancelled projects
                                                $query->whereNotIn('status', ['completed', 'cancelled']);
                                                
                                                // Role-based filtering
                                                if ($user->hasRole(['admin', 'redaksi'])) {
                                                    return $query;
                                                }
                                                
                                                if ($user->hasRole('team')) {
                                                    return $query->where(function ($subQuery) use ($user) {
                                                        $subQuery->where('project_manager_id', $user->id)
                                                            ->orWhere('created_by', $user->id)
                                                            ->orWhereJsonContains('team_members', (string) $user->id);
                                                    });
                                                }
                                                
                                                return $query->whereRaw('1 = 0');
                                            }
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Pilih project yang sedang berjalan')
                                        ->helperText('Hanya project yang masih aktif yang dapat dipilih')
                                        ->getOptionLabelFromRecordUsing(function ($record) {
                                            $label = $record->nama_project;
                                            
                                            $statusBadge = match($record->status) {
                                                'draft' => '📝 Draft',
                                                'planning' => '📋 Planning', 
                                                'in_progress' => '🚀 In Progress',
                                                'review' => '👁️ Review',
                                                default => ucfirst($record->status)
                                            };
                                            
                                            $label .= " ({$statusBadge})";
                                            
                                            if ($record->tanggal_selesai) {
                                                $deadline = $record->tanggal_selesai->format('d M Y');
                                                $label .= " - Deadline: {$deadline}";
                                            }
                                            
                                            return $label;
                                        }),

                                    Forms\Components\DatePicker::make('tanggal_dibutuhkan')
                                        ->label('Tanggal Dibutuhkan')
                                        ->required()
                                        ->native(false)
                                        ->minDate(now())
                                        ->helperText('Kapan anggaran ini dibutuhkan'),

                                    Forms\Components\Select::make('priority')
                                        ->label('Prioritas')
                                        ->options([
                                            'low' => 'Rendah',
                                            'medium' => 'Sedang', 
                                            'high' => 'Tinggi',
                                            'urgent' => 'Mendesak'
                                        ])
                                        ->default('medium')
                                        ->required(),
                                ]),

                            Forms\Components\Section::make('Deskripsi & Justifikasi')
                                ->schema([
                                    Forms\Components\Textarea::make('deskripsi')
                                        ->label('Deskripsi Kebutuhan')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder('Jelaskan secara detail kebutuhan anggaran ini')
                                        ->helperText('Deskripsikan apa yang akan dibeli/dilakukan'),

                                    Forms\Components\Textarea::make('justifikasi')
                                        ->label('Justifikasi Bisnis')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder('Jelaskan mengapa anggaran ini penting untuk bisnis')
                                        ->helperText('Manfaat dan dampak bisnis dari pengajuan ini'),
                                ]),

                            // Total Anggaran Display
                            Forms\Components\Section::make('Ringkasan Anggaran')
                                ->schema([
                                    Forms\Components\Placeholder::make('total_summary')
                                        ->label('')
                                        ->content(function (Forms\Get $get) {
                                            $items = $get('detail_items') ?? [];
                                            $total = 0;
                                            $itemCount = 0;

                                            foreach ($items as $item) {
                                                if (!empty($item['item_name'])) {
                                                    $total += (float) ($item['total_price'] ?? 0);
                                                    $itemCount++;
                                                }
                                            }

                                            return new \Illuminate\Support\HtmlString('
                                                <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-700">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Total Items:</span>
                                                        <span class="text-sm font-semibold text-orange-900 dark:text-orange-100">' . $itemCount . ' item(s)</span>
                                                    </div>
                                                    <div class="flex justify-between items-center border-t border-orange-200 dark:border-orange-700 pt-2 mt-2">
                                                        <span class="text-lg font-bold text-orange-900 dark:text-orange-100">TOTAL ANGGARAN:</span>
                                                        <span class="text-xl font-bold text-orange-600 dark:text-orange-400">Rp ' . number_format($total, 0, ',', '.') . '</span>
                                                    </div>
                                                </div>
                                            ');
                                        }),

                                    // Hidden field untuk menyimpan total
                                    Forms\Components\TextInput::make('total_anggaran')
                                        ->hidden()
                                        ->live()
                                        ->afterStateHydrated(function ($component, callable $get) {
                                            $items = $get('detail_items') ?? [];
                                            $total = 0;

                                            if (is_array($items)) {
                                                foreach ($items as $item) {
                                                    $total += (float) ($item['total_price'] ?? 0);
                                                }
                                            }

                                            $component->state($total);
                                        }),
                                ]),
                        ])
                        ->columnSpan(1),

                    // KOLOM KANAN
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make('Detail Item Anggaran')
                                ->description('Breakdown detail anggaran yang diajukan')
                                ->schema([
                                    Forms\Components\Repeater::make('detail_items')
                                        ->label('Item Anggaran')
                                        ->schema([
                                            // Budget Selection (2 kolom)
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
                                                    if (!$categoryId) return [];

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
                                                ->placeholder('Nama barang/jasa')
                                                ->columnSpan(4),

                                            // Quantity & Price (4 kolom)
                                            Forms\Components\TextInput::make('quantity')
                                                ->label('Qty')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                                    $quantity = (float) ($state ?: 1);
                                                    $unitPrice = (float) ($get('unit_price') ?: 0);
                                                    $set('total_price', $quantity * $unitPrice);
                                                }),

                                            Forms\Components\TextInput::make('unit_price')
                                                ->label('Harga Satuan')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->required()
                                                ->placeholder('0')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                                    $quantity = (float) ($get('quantity') ?: 1);
                                                    $unitPrice = (float) ($state ?: 0);
                                                    $set('total_price', $quantity * $unitPrice);
                                                }),

                                            Forms\Components\TextInput::make('total_price')
                                                ->label('Total Harga')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->placeholder('Auto calculated'),

                                            Forms\Components\TextInput::make('unit')
                                                ->label('Satuan')
                                                ->placeholder('pcs, kg, meter')
                                                ->default('pcs'),

                                            Forms\Components\Textarea::make('description')
                                                ->label('Spesifikasi')
                                                ->rows(2)
                                                ->maxLength(500)
                                                ->placeholder('Detail spesifikasi item')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(4)
                                        ->addActionLabel('+ Tambah Item')
                                        ->reorderableWithButtons()
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            ($state['item_name'] ?? 'Item Baru') .
                                            (isset($state['total_price']) ? ' - Rp ' . number_format($state['total_price'], 0, ',', '.') : '')
                                        )
                                        ->defaultItems(1)
                                        ->minItems(1)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Calculate total from all items
                                            $total = 0;
                                            if (is_array($state)) {
                                                foreach ($state as $item) {
                                                    $total += (float) ($item['total_price'] ?? 0);
                                                }
                                            }
                                            $set('total_anggaran', $total);
                                        }),
                                ]),

                            // Status Approval Section (hanya untuk edit/view)
                            Forms\Components\Section::make('Status Approval')
                                ->schema([
                                    Forms\Components\Placeholder::make('workflow_status')
                                        ->label('Status Workflow')
                                        ->content(function ($record) {
                                            if (!$record) return '📝 Draft - Belum diajukan';

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
                                ->visible(fn($context) => $context === 'edit' || $context === 'view'),
                        ])
                        ->columnSpan(1),
                ])
        ])
        ->extraAttributes([
            'x-data' => '{
                init() {
                    this.$watch("$wire.data.detail_items", () => {
                        let total = 0;
                        if (this.$wire.data.detail_items) {
                            this.$wire.data.detail_items.forEach(item => {
                                total += parseFloat(item.total_price || 0);
                            });
                        }
                        this.$wire.set("data.total_anggaran", total);
                    });
                }
            }'
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
                    ->query(fn(Builder $query): Builder => $query->where('created_by', auth()->id())),

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
                            DB::transaction(function () use ($record, $data) {
                                // 1. Update status pengajuan
                                $record->update([
                                    'status' => 'approved',
                                    'keuangan_approved_by' => auth()->id(),
                                    'keuangan_approved_at' => now(),
                                    'keuangan_notes' => $data['keuangan_notes'] ?? null,
                                ]);

                                // 2. Create transaksi pengeluaran
                                $transaksi = \App\Models\Transaksi::create([
                                    'nomor_transaksi' => self::generateNomorTransaksi(),
                                    'jenis_transaksi' => 'pengeluaran',
                                    'tanggal_transaksi' => now(),
                                    'nama_transaksi' => 'Pengeluaran: ' . $record->judul_pengajuan,
                                    'deskripsi' => $record->deskripsi,
                                    'total_amount' => $record->total_anggaran,
                                    'status' => 'approved',
                                    'metode_pembayaran' => 'transfer',
                                    'project_id' => $record->project_id,
                                    'pengajuan_anggaran_id' => $record->id,
                                    'created_by' => auth()->id(),
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                    'workflow_type' => 'pengajuan_anggaran',
                                    'catatan_approval' => 'Disetujui melalui pengajuan anggaran: ' . $record->nomor_pengajuan,
                                ]);

                                // 3. Create transaksi items
                                foreach ($record->detail_items as $item) {
                                    \App\Models\TransaksiItem::create([
                                        'transaksi_id' => $transaksi->id,
                                        'nama_item' => $item['item_name'] ?? $item['nama_item'] ?? 'Item',
                                        'kuantitas' => $item['quantity'] ?? $item['kuantitas'] ?? 1,
                                        'harga_satuan' => $item['unit_price'] ?? $item['harga_satuan'] ?? 0,
                                        'subtotal' => $item['total_price'] ?? 0,
                                        'satuan' => 'pcs',
                                        'deskripsi_item' => $item['description'] ?? $item['spesifikasi'] ?? null,
                                    ]);
                                }

                                // 4. Update budget allocations
                                foreach ($record->detail_items as $item) {
                                    if (isset($item['budget_subcategory_id'])) {
                                        $allocation = BudgetAllocation::where('budget_subcategory_id', $item['budget_subcategory_id'])
                                            ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
                                            ->first();

                                        if ($allocation) {
                                            $allocation->increment('used_amount', $item['total_price']);

                                            // Link transaksi ke budget allocation
                                            if (!$transaksi->budget_allocation_id) {
                                                $transaksi->update(['budget_allocation_id' => $allocation->id]);
                                            }
                                        }
                                    }
                                }
                            });

                            Notification::make()
                                ->title('Pengajuan Final Approved!')
                                ->body("Pengajuan '{$record->judul_pengajuan}' telah disetujui, budget dialokasikan, dan transaksi pengeluaran dibuat.")
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

        // Redaksi bisa lihat semua pengajuan kecuali yang masih draft
        if ($user->hasRole(['redaksi'])) {
            return parent::getEloquentQuery()
                ->whereIn('status', ['pending_redaksi', 'pending_keuangan', 'approved', 'rejected']);
        }

        // Keuangan/Direktur bisa lihat pengajuan yang sudah lewat tahap redaksi
        if ($user->hasRole(['keuangan', 'direktur'])) {
            return parent::getEloquentQuery()
                ->whereIn('status', ['pending_keuangan', 'approved', 'rejected']);
        }

        // Team bisa lihat pengajuan mereka sendiri semua status
        // + pengajuan yang sudah approved dari team lain (untuk referensi)
        return parent::getEloquentQuery()
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('status', 'approved');
            });
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

    private static function generateNomorTransaksi(): string
    {
        $prefix = 'TRX-OUT';
        $year = now()->format('Y');
        $month = now()->format('m');

        $counter = \App\Models\Transaksi::whereYear('tanggal_transaksi', now()->year)
            ->whereMonth('tanggal_transaksi', now()->month)
            ->where('jenis_transaksi', 'pengeluaran')
            ->count() + 1;

        return $prefix . '/' . $year . '/' . $month . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }
}