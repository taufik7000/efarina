<?php

namespace App\Filament\Resources\BudgetPlanResource\Pages;

use App\Filament\Resources\BudgetPlanResource;
use App\Filament\Resources\BudgetAllocationResource;
use App\Models\BudgetAllocation;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewBudgetPlan extends ViewRecord
{
    protected static string $resource = BudgetPlanResource::class;
public function getTitle(): string
{
    return $this->getRecord()->nama_budget;
}

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),

            Actions\Action::make('increase_total_budget')
            ->label('Tambah Total Budget')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->visible(fn () => auth()->user()->hasRole(['direktur', 'keuangan']))
            ->modal()
            ->modalHeading('Tambah Total Budget')
            ->modalDescription('Menambah total budget akan menambah sisa budget yang bisa dialokasikan')
            ->modalWidth('md')
            ->modalSubmitActionLabel('Tambah Budget')
            ->modalCancelActionLabel('Batal')
            ->form([
                Forms\Components\Section::make('Informasi Budget Saat Ini')
                    ->schema([
                        Forms\Components\Placeholder::make('current_budget_info')
                            ->label('')
                            ->content(function () {
                                $record = $this->getRecord();
                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2 p-4 bg-blue-50 rounded-lg">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Total Budget Saat Ini:</span>
                                            <span class="font-bold text-blue-600">Rp ' . number_format($record->total_budget, 0, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Total Dialokasikan:</span>
                                            <span class="font-bold text-yellow-600">Rp ' . number_format($record->total_allocated, 0, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between border-t pt-2">
                                            <span class="font-medium">Sisa Budget:</span>
                                            <span class="font-bold text-green-600">Rp ' . number_format($record->remaining_budget, 0, ',', '.') . '</span>
                                        </div>
                                    </div>
                                ');
                            })
                    ]),

                Forms\Components\Section::make('Penambahan Budget')
                    ->schema([
                        Forms\Components\TextInput::make('additional_budget')
                            ->label('Tambahan Budget')
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->required()
                            ->placeholder('0')
                            ->extraInputAttributes([
                                'oninput' => "
                                    let value = this.value.replace(/[^0-9]/g, '');
                                    if (value) {
                                        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                    }
                                ",
                                'onkeydown' => "
                                    if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
                                        (event.keyCode === 65 && event.ctrlKey === true) ||
                                        (event.keyCode === 67 && event.ctrlKey === true) ||
                                        (event.keyCode === 86 && event.ctrlKey === true) ||
                                        (event.keyCode === 88 && event.ctrlKey === true)) {
                                        return;
                                    }
                                    if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                                        event.preventDefault();
                                    }
                                "
                            ])
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->rules(['min:1'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $additionalBudget = (int) str_replace('.', '', (string) $state);
                                $currentBudget = $this->getRecord()->total_budget;
                                $newTotal = $currentBudget + $additionalBudget;
                                $set('new_total_budget', $newTotal);
                            }),

                        Forms\Components\Placeholder::make('new_total_preview')
                            ->label('Preview Total Budget Baru')
                            ->content(function (Forms\Get $get) {
                                $additionalBudget = $get('additional_budget') ? 
                                    (int) str_replace('.', '', (string) $get('additional_budget')) : 0;
                                $currentBudget = $this->getRecord()->total_budget;
                                $newTotal = $currentBudget + $additionalBudget;
                                $newRemaining = $newTotal - $this->getRecord()->total_allocated;
                                
                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2 p-4 bg-green-50 rounded-lg">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Total Budget Baru:</span>
                                            <span class="font-bold text-green-600">Rp ' . number_format($newTotal, 0, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between border-t pt-2">
                                            <span class="font-medium">Sisa Budget Baru:</span>
                                            <span class="font-bold text-blue-600">Rp ' . number_format($newRemaining, 0, ',', '.') . '</span>
                                        </div>
                                    </div>
                                ');
                            }),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penambahan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan penambahan total budget ini...'),

                        Forms\Components\Hidden::make('new_total_budget'),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $record = $this->getRecord();
                    $additionalBudget = $data['additional_budget'];
                    $oldTotal = $record->total_budget;
                    $newTotal = $oldTotal + $additionalBudget;

                    // Update total budget
                    $record->update([
                        'total_budget' => $newTotal
                    ]);

                    $record->logBudgetIncrease($additionalBudget, $data['reason']);

                    

                    // Log activity (opsional)
                    \Log::info('Budget Plan total increased', [
                        'budget_plan_id' => $record->id,
                        'budget_plan_name' => $record->nama_budget,
                        'old_total' => $oldTotal,
                        'additional_amount' => $additionalBudget,
                        'new_total' => $newTotal,
                        'reason' => $data['reason'],
                        'updated_by' => auth()->id(),
                        'updated_by_name' => auth()->user()->name
                    ]);

                    Notification::make()
                        ->title('Total Budget Berhasil Ditambah')
                        ->body(
                            'Total budget berhasil ditambah dari Rp ' . number_format($oldTotal, 0, ',', '.') . 
                            ' menjadi Rp ' . number_format($newTotal, 0, ',', '.') . 
                            ' (+Rp ' . number_format($additionalBudget, 0, ',', '.') . ')'
                        )
                        ->success()
                        ->duration(8000)
                        ->send();

                    // Refresh halaman untuk update data
                    return redirect(request()->header('Referer'));

                } catch (\Exception $e) {
                    \Log::error('Error increasing total budget: ' . $e->getMessage());
                    
                    Notification::make()
                        ->title('Terjadi Kesalahan')
                        ->body('Gagal menambah total budget: ' . $e->getMessage())
                        ->danger()
                        ->duration(8000)
                        ->send();
                    
                    return false;
                }
            }),
                
            Actions\Action::make('create_allocation')
                ->label('Tambah Alokasi')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole(['keuangan', 'direktur']))
                ->modal()
                ->modalHeading('Tambah Alokasi Budget')
                ->modalDescription('Buat alokasi budget baru atau tambahkan budget ke alokasi yang sudah ada')
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('Simpan Alokasi')
                ->modalCancelActionLabel('Batal')
                ->form([
                    Forms\Components\Section::make('Informasi Alokasi')
                        ->schema([
                            Forms\Components\Hidden::make('budget_plan_id')
                                ->default(fn () => $this->getRecord()->id),
                                
                            Forms\Components\Select::make('budget_category_id')
                                ->label('Kategori Budget')
                                ->options(function () {
                                    return \App\Models\BudgetCategory::where('is_active', true)
                                        ->pluck('nama_kategori', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                    $set('budget_subcategory_id', null);
                                    $this->checkExistingAllocation($get, $set);
                                }),

                            Forms\Components\Select::make('budget_subcategory_id')
                                ->label('Subkategori Budget')
                                ->options(fn (Forms\Get $get): array => 
                                    $get('budget_category_id') 
                                        ? \App\Models\BudgetCategory::find($get('budget_category_id'))
                                            ?->subcategories()
                                            ->where('is_active', true)
                                            ->pluck('nama_subkategori', 'id')
                                            ->toArray() ?? []
                                        : []
                                )
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                    $this->checkExistingAllocation($get, $set);
                                }),

                            // Alert untuk menampilkan info alokasi existing
                            Forms\Components\Placeholder::make('existing_allocation_info')
                                ->label('')
                                ->content(function (Forms\Get $get) {
                                    $budgetPlanId = $get('budget_plan_id');
                                    $categoryId = $get('budget_category_id');
                                    $subcategoryId = $get('budget_subcategory_id');
                                    
                                    if ($budgetPlanId && $categoryId) {
                                        $existing = BudgetAllocation::where('budget_plan_id', $budgetPlanId)
                                            ->where('budget_category_id', $categoryId)
                                            ->where('budget_subcategory_id', $subcategoryId)
                                            ->first();
                                            
                                        if ($existing) {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                                    <div class="flex items-center">
                                                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <div>
                                                            <h4 class="text-sm font-medium text-blue-800">Alokasi Sudah Ada</h4>
                                                            <p class="text-sm text-blue-600">
                                                                Alokasi saat ini: <strong>Rp ' . number_format($existing->allocated_amount, 0, ',', '.') . '</strong><br>
                                                                Jumlah yang akan Anda masukkan akan <strong>ditambahkan</strong> ke alokasi existing.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            ');
                                        }
                                    }
                                    return '';
                                })
                                ->visible(fn (Forms\Get $get) => $this->hasExistingAllocation($get)),

                            Forms\Components\TextInput::make('allocated_amount')
                                ->label('Jumlah Alokasi')
                                ->prefix('Rp')
                                ->inputMode('decimal')
                                ->required()
                                ->placeholder('0')
                                ->extraInputAttributes([
                                    'oninput' => "
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        ",
                                    'onkeydown' => "
            if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();
            }
        "
                                ])
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                                ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                                ->helperText(function () {
                                    return 'Sisa budget: Rp ' . number_format($this->getRecord()->remaining_budget, 0, ',', '.');
                                }),

                            Forms\Components\Textarea::make('catatan')
                                ->label('Catatan')
                                ->placeholder('Catatan tambahan untuk alokasi ini...')
                                ->rows(3),
                        ])
                ])
                ->action(function (array $data) {
                    try {
                        // Validasi remaining budget
                        $budgetPlan = $this->getRecord();
                        if ($data['allocated_amount'] > $budgetPlan->remaining_budget) {
                            Notification::make()
                                ->title('Alokasi Melebihi Budget')
                                ->body('Sisa budget hanya Rp ' . number_format($budgetPlan->remaining_budget, 0, ',', '.'))
                                ->warning()
                                ->duration(8000)
                                ->send();
                            return false;
                        }

                        // Check if allocation already exists
                        $existingAllocation = BudgetAllocation::where('budget_plan_id', $data['budget_plan_id'])
                            ->where('budget_category_id', $data['budget_category_id'])
                            ->where('budget_subcategory_id', $data['budget_subcategory_id'])
                            ->first();

                        if ($existingAllocation) {
                            // Update existing allocation - ADD to current amount
                            $oldAmount = $existingAllocation->allocated_amount;
                            $existingAllocation->increment('allocated_amount', $data['allocated_amount']);
                            $existingAllocation->logAllocationIncrease($data['allocated_amount'], $data['catatan']);
                            
                            // Update catatan jika ada
                            if (!empty($data['catatan'])) {
                                $newCatatan = $existingAllocation->catatan 
                                    ? $existingAllocation->catatan . "\n\n[" . now()->format('d/m/Y H:i') . "] Penambahan: " . $data['catatan']
                                    : $data['catatan'];
                                $existingAllocation->update(['catatan' => $newCatatan]);
                            }

                            // Update budget plan totals
                            $budgetPlan->updateTotals();

                            Notification::make()
                                ->title('Alokasi Berhasil Ditambahkan')
                                ->body(
                                    'Alokasi berhasil ditambahkan ke kategori existing.<br>' .
                                    'Sebelum: Rp ' . number_format($oldAmount, 0, ',', '.') . '<br>' .
                                    'Ditambah: Rp ' . number_format($data['allocated_amount'], 0, ',', '.') . '<br>' .
                                    '<strong>Total sekarang: Rp ' . number_format($existingAllocation->fresh()->allocated_amount, 0, ',', '.') . '</strong>'
                                )
                                ->success()
                                ->duration(8000)
                                ->send();

                        } else {
                            // Create new allocation
                            $data['created_by'] = auth()->id();
                            $data['used_amount'] = 0;

                            $allocation = BudgetAllocation::create($data);

                            // Update budget plan totals
                            $budgetPlan->updateTotals();

                            Notification::make()
                                ->title('Alokasi Berhasil Dibuat')
                                ->body('Alokasi baru sebesar Rp ' . number_format($data['allocated_amount'], 0, ',', '.') . ' telah ditambahkan')
                                ->success()
                                ->duration(5000)
                                ->send();
                        }

                        return redirect(request()->header('Referer'));

                    } catch (\Exception $e) {
                        \Log::error('Error creating/updating budget allocation: ' . $e->getMessage());
                        
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body('Gagal memproses alokasi: ' . $e->getMessage())
                            ->danger()
                            ->duration(8000)
                            ->send();
                        
                    return false;
                }
            }),

        Actions\Action::make('export_report')
            ->label('Export Laporan')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->action(function () {
                return response()->streamDownload(function () {
                    echo $this->generateBudgetReport();
                }, 'budget-plan-' . $this->getRecord()->id . '.txt');
            }),
    ];
}

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Main Grid Layout
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Left Column - Detail Alokasi Budget (Lebih Lebar)
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Detail Alokasi Budget')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('allocations_table')
                                        ->label('')
                                        ->view('filament.components.budget-allocations-table')
                                        ->state(fn ($record) => $record->allocations()->with(['category', 'subcategory'])->get())
                                ])
                                ->collapsible(),
                                
                            // Detail Transaksi Budget Plan
                            Infolists\Components\Section::make('Transaksi Terkait')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('transactions_table')
                                        ->label('')
                                        ->view('filament.components.budget-transactions-table')
                                        ->state(fn ($record) => $this->getBudgetTransactions($record))
                                ]),
                        ])
                        ->columnSpan(2), // Mengambil 2 kolom dari 3 (lebih lebar)
                        
                        // Right Column - Info & Summary
                        Infolists\Components\Group::make([
                                                        // Ringkasan Budget
                            Infolists\Components\Section::make('Ringkasan Budget')
                                ->schema([
                                    Infolists\Components\TextEntry::make('total_budget')
                                        ->label('Total Budget')
                                        ->money('IDR')
                                        ->weight(FontWeight::Bold)
                                        ->color('primary')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        
                                    Infolists\Components\Fieldset::make('Alokasi & Penggunaan')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('total_allocated')
                                                ->label('Dialokasikan')
                                                ->money('IDR')
                                                ->color('info'),
                                                
                                            Infolists\Components\TextEntry::make('total_used')
                                                ->label('Terpakai')
                                                ->money('IDR')
                                                ->color('warning'),
                                                
                                            Infolists\Components\TextEntry::make('remaining_budget')
                                                ->label('Sisa Budget')
                                                ->money('IDR')
                                                ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                                                ->weight(FontWeight::SemiBold),
                                        ]),
                                        
                                    Infolists\Components\Fieldset::make('Persentase')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('allocation_percentage')
                                                ->label('% Alokasi')
                                                ->suffix('%')
                                                ->color(fn ($state) => $state >= 100 ? 'danger' : ($state >= 80 ? 'warning' : 'success'))
                                                ->badge(),
                                                
                                            Infolists\Components\TextEntry::make('usage_percentage')
                                                ->label('% Penggunaan')
                                                ->suffix('%')
                                                ->color(fn ($state) => $state >= 90 ? 'danger' : ($state >= 75 ? 'warning' : 'success'))
                                                ->badge(),
                                        ]),
                                ])
                                ->compact(),
                            // Informasi Budget Plan
                            Infolists\Components\Section::make('Informasi Budget Plan')
                                ->schema([
                                    Infolists\Components\TextEntry::make('nama_budget')
                                        ->label('Nama Budget')
                                        ->weight(FontWeight::Bold)
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->color('primary'),
                                        
                                    Infolists\Components\TextEntry::make('period.nama_periode')
                                        ->label('Periode')
                                        ->badge()
                                        ->color('info'),
                                        
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'draft' => 'gray',
                                            'active' => 'success',
                                            'completed' => 'info',
                                            'cancelled' => 'danger',
                                            default => 'gray',
                                        }),
                                        
                                    Infolists\Components\TextEntry::make('deskripsi')
                                        ->label('Deskripsi')
                                        ->placeholder('Tidak ada deskripsi'),
                                ])
                                ->compact(),

                            Infolists\Components\Section::make('Audit Trail')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('audit_trails_table')
                                        ->label('')
                                        ->view('filament.components.budget-audit-trails')
                                        ->state(fn($record) => $record->auditTrails()->with('user')->limit(10)->get())
                                ]),

                            // Informasi Sistem
                            Infolists\Components\Section::make('Informasi Sistem')
                                ->schema([
                                    Infolists\Components\TextEntry::make('createdBy.name')
                                        ->label('Dibuat Oleh')
                                        ->placeholder('Tidak diketahui')
                                        ->icon('heroicon-o-user'),
                                        
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Dibuat Pada')
                                        ->dateTime('d M Y H:i')
                                        ->icon('heroicon-o-calendar'),
                                        
                                    Infolists\Components\TextEntry::make('updated_at')
                                        ->label('Diperbarui')
                                        ->dateTime('d M Y H:i')
                                        ->icon('heroicon-o-clock'),
                                ])
                                ->compact(),

                            // Informasi Persetujuan (jika ada)
                            Infolists\Components\Section::make('Informasi Persetujuan')
                                ->schema([
                                    Infolists\Components\TextEntry::make('approved_at')
                                        ->label('Tanggal Disetujui')
                                        ->dateTime('d M Y H:i')
                                        ->placeholder('Belum disetujui')
                                        ->icon('heroicon-o-check-circle'),
                                        
                                    Infolists\Components\TextEntry::make('approvedBy.name')
                                        ->label('Disetujui Oleh')
                                        ->placeholder('Belum disetujui')
                                        ->icon('heroicon-o-user-check'),
                                        
                                    Infolists\Components\TextEntry::make('approval_notes')
                                        ->label('Catatan Persetujuan')
                                        ->placeholder('Tidak ada catatan'),
                                ])
                                ->compact()
                                ->visible(fn ($record) => $record->approved_at || $record->approved_by),
                        ])
                        ->columnSpan(1), // Mengambil 1 kolom dari 3
                    ]),
            ]);
    }

    public function getContentTabLabel(): ?string
    {
        return 'Detail Budget Plan';
    }

    /**
     * Generate budget report for export
     */
    private function generateBudgetReport(): string
    {
        $record = $this->getRecord();
        $allocations = $record->allocations()->with(['category', 'subcategory'])->get();
        
        $report = "LAPORAN BUDGET PLAN\n";
        $report .= "===================\n\n";
        $report .= "Nama Budget: {$record->nama_budget}\n";
        $report .= "Periode: {$record->period->nama_periode}\n";
        $report .= "Total Budget: Rp " . number_format($record->total_budget, 0, ',', '.') . "\n";
        $report .= "Total Dialokasikan: Rp " . number_format($record->total_allocated, 0, ',', '.') . "\n";
        $report .= "Total Terpakai: Rp " . number_format($record->total_used, 0, ',', '.') . "\n";
        $report .= "Sisa Budget: Rp " . number_format($record->remaining_budget, 0, ',', '.') . "\n\n";
        
        $report .= "DETAIL ALOKASI:\n";
        $report .= "===============\n\n";
        
        foreach ($allocations as $allocation) {
            $report .= "Kategori: {$allocation->category->nama_kategori}\n";
            if ($allocation->subcategory) {
                $report .= "Sub Kategori: {$allocation->subcategory->nama_subkategori}\n";
            }
            $report .= "Alokasi: Rp " . number_format($allocation->allocated_amount, 0, ',', '.') . "\n";
            $report .= "Terpakai: Rp " . number_format($allocation->used_amount, 0, ',', '.') . "\n";
            $report .= "Sisa: Rp " . number_format($allocation->remaining_amount, 0, ',', '.') . "\n";
            $report .= "Persentase: {$allocation->usage_percentage}%\n";
            if ($allocation->catatan) {
                $report .= "Catatan: {$allocation->catatan}\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }

    /**
     * Get transactions related to budget plan
     */
    private function getBudgetTransactions($record)
    {
        try {
            return \App\Models\Transaksi::with([
                'budgetAllocation.category', 
                'budgetAllocation.subcategory',
                'createdBy'
            ])
            ->whereHas('budgetAllocation', function($query) use ($record) {
                $query->where('budget_plan_id', $record->id);
            })
            ->where('jenis_transaksi', 'pengeluaran')
            ->orderBy('tanggal_transaksi', 'desc')
            ->limit(20) // Batasi 20 transaksi terbaru
            ->get();
        } catch (\Exception $e) {
            // Jika ada error, return collection kosong
            return collect();
        }
    }

       /**
     * Helper method to check if allocation already exists
     */
    private function hasExistingAllocation(Forms\Get $get): bool
    {
        $budgetPlanId = $get('budget_plan_id');
        $categoryId = $get('budget_category_id');
        $subcategoryId = $get('budget_subcategory_id');
        
        if ($budgetPlanId && $categoryId) {
            return BudgetAllocation::where('budget_plan_id', $budgetPlanId)
                ->where('budget_category_id', $categoryId)
                ->where('budget_subcategory_id', $subcategoryId)
                ->exists();
        }
        
        return false;
    }

    /**
     * Helper method to check existing allocation and update form state
     */
    private function checkExistingAllocation(Forms\Get $get, Forms\Set $set): void
    {
        $budgetPlanId = $get('budget_plan_id');
        $categoryId = $get('budget_category_id');
        $subcategoryId = $get('budget_subcategory_id');
        
        if ($budgetPlanId && $categoryId) {
            $existing = BudgetAllocation::where('budget_plan_id', $budgetPlanId)
                ->where('budget_category_id', $categoryId)
                ->where('budget_subcategory_id', $subcategoryId)
                ->first();
                
            // Force refresh the form to show/hide the info placeholder
            // This is handled by the reactive form components
        }
    }

}