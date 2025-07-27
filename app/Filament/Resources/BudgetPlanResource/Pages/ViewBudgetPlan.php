<?php

namespace App\Filament\Resources\BudgetPlanResource\Pages;

use App\Filament\Resources\BudgetPlanResource;
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
                ->color('warning')
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
                                    "
                                ])
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                                ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                                ->rules(['min:1']),

                            Forms\Components\Textarea::make('reason')
                                ->label('Alasan Penambahan')
                                ->required()
                                ->rows(3)
                                ->placeholder('Jelaskan alasan penambahan total budget ini...'),
                        ])
                ])
                ->action(function (array $data) {
                    try {
                        $record = $this->getRecord();
                        $additionalBudget = $data['additional_budget'];
                        $oldTotal = $record->total_budget;
                        $newTotal = $oldTotal + $additionalBudget;

                        $record->update(['total_budget' => $newTotal]);

                        // Log audit trail
                        if (method_exists($record, 'logBudgetIncrease')) {
                            $record->logBudgetIncrease($additionalBudget, $data['reason']);
                        }

                        Notification::make()
                            ->title('Total Budget Berhasil Ditambah')
                            ->body('Total budget berhasil ditambah sebesar Rp ' . number_format($additionalBudget, 0, ',', '.'))
                            ->success()
                            ->duration(8000)
                            ->send();

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
                                ->searchable(),

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

                        $existingAllocation = BudgetAllocation::where('budget_plan_id', $data['budget_plan_id'])
                            ->where('budget_category_id', $data['budget_category_id'])
                            ->where('budget_subcategory_id', $data['budget_subcategory_id'])
                            ->first();

                        if ($existingAllocation) {
                            $oldAmount = $existingAllocation->allocated_amount;
                            $existingAllocation->increment('allocated_amount', $data['allocated_amount']);
                            
                            if (!empty($data['catatan'])) {
                                $newCatatan = $existingAllocation->catatan 
                                    ? $existingAllocation->catatan . "\n\n[" . now()->format('d/m/Y H:i') . "] Penambahan: " . $data['catatan']
                                    : $data['catatan'];
                                $existingAllocation->update(['catatan' => $newCatatan]);
                            }

                            // Log audit untuk penambahan
                            if (method_exists($existingAllocation, 'logAllocationIncrease')) {
                                $existingAllocation->logAllocationIncrease($data['allocated_amount'], $data['catatan']);
                            }

                            $budgetPlan->updateTotals();

                            Notification::make()
                                ->title('Alokasi Berhasil Ditambahkan')
                                ->body('Alokasi berhasil ditambahkan ke kategori existing.')
                                ->success()
                                ->duration(8000)
                                ->send();

                        } else {
                            $data['created_by'] = auth()->id();
                            $data['used_amount'] = 0;

                            $allocation = BudgetAllocation::create($data);
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

    // TAMBAHAN: Method untuk handle AJAX edit allocation
    public function updateAllocation()
    {
        try {
            $data = request()->validate([
                'id' => 'required|exists:budget_allocations,id',
                'allocated_amount' => 'required|numeric|min:0',
                'catatan' => 'nullable|string',
                'reason' => 'required|string|min:10'
            ]);

            $allocation = BudgetAllocation::findOrFail($data['id']);
            $oldAmount = $allocation->allocated_amount;
            $newAmount = (float) $data['allocated_amount'];
            $amountChanged = $newAmount - $oldAmount;

            // Check budget availability for increases
            if ($amountChanged > 0 && $amountChanged > $allocation->budgetPlan->remaining_budget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penambahan melebihi sisa budget yang tersedia'
                ], 400);
            }

            $allocation->update([
                'allocated_amount' => $newAmount,
                'catatan' => $data['catatan']
            ]);

            $allocation->budgetPlan->updateTotals();

            // Log audit trail
            if (method_exists($allocation, 'logAudit')) {
                $action = $amountChanged > 0 ? 'allocation_increased' : 'allocation_decreased';
                $description = "Alokasi budget diubah dari Rp " . number_format($oldAmount, 0, ',', '.') . 
                              " menjadi Rp " . number_format($newAmount, 0, ',', '.') . 
                              " (" . ($amountChanged > 0 ? '+' : '') . "Rp " . number_format($amountChanged, 0, ',', '.') . ")";

                $allocation->logAudit(
                    $action,
                    ['allocated_amount' => $oldAmount],
                    ['allocated_amount' => $newAmount],
                    $amountChanged,
                    $description,
                    $data['reason']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Alokasi budget berhasil diperbarui',
                'data' => [
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'amount_changed' => $amountChanged
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating budget allocation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // TAMBAHAN: Method untuk delete allocation
    public function deleteAllocation()
    {
        try {
            $data = request()->validate([
                'id' => 'required|exists:budget_allocations,id',
                'reason' => 'required|string|min:10'
            ]);

            $allocation = BudgetAllocation::findOrFail($data['id']);
            
            // Check if allocation is being used
            if ($allocation->used_amount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus alokasi yang sudah digunakan'
                ], 400);
            }

            $budgetPlan = $allocation->budgetPlan;
            $deletedAmount = $allocation->allocated_amount;
            $categoryName = $allocation->category_name;

            // Log audit before delete
            if (method_exists($allocation, 'logAudit')) {
                $allocation->logAudit(
                    'allocation_deleted',
                    $allocation->toArray(),
                    null,
                    -$deletedAmount,
                    "Alokasi budget untuk {$categoryName} sebesar Rp " . number_format($deletedAmount, 0, ',', '.') . " telah dihapus",
                    $data['reason']
                );
            }

            $allocation->delete();
            $budgetPlan->updateTotals();

            return response()->json([
                'success' => true,
                'message' => 'Alokasi budget berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting budget allocation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus alokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Detail Alokasi Budget')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('allocations_table')
                                        ->label('')
                                        ->view('filament.components.budget-allocations-table')
                                        ->state(fn ($record) => $record->allocations()->with(['category', 'subcategory'])->get())
                                ])
                                ->collapsible(),
                                
                            Infolists\Components\Section::make('Transaksi Terkait')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('transactions_table')
                                        ->label('')
                                        ->view('filament.components.budget-transactions-table')
                                        ->state(fn ($record) => $this->getBudgetTransactions($record))
                                ]),
                        ])
                        ->columnSpan(2),
                        
                        Infolists\Components\Group::make([
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
                                        ->state(function ($record) {
                                            try {
                                                if (!method_exists($record, 'auditTrails')) {
                                                    return collect();
                                                }
                                                
                                                return $record->auditTrails()
                                                    ->with('user')
                                                    ->latest()
                                                    ->limit(10)
                                                    ->get();
                                            } catch (\Exception $e) {
                                                \Log::warning('Failed to load audit trails: ' . $e->getMessage());
                                                return collect();
                                            }
                                        })
                                ]),
                        ])
                        ->columnSpan(1),
                    ]),
            ]);
    }

    // ... existing methods (generateBudgetReport, getBudgetTransactions, etc.)
    
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
            ->limit(20)
            ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function editAllocationAction(): Actions\Action
{
    return Actions\Action::make('editAllocation')
        ->label('Edit Alokasi')
        ->icon('heroicon-o-pencil-square')
        ->color('warning')
        ->modal()
        ->modalHeading('Edit Alokasi Budget')
        ->modalWidth('md')
        ->form(function (array $arguments) {
            $allocationId = $arguments['allocation'] ?? null;
            $allocation = BudgetAllocation::find($allocationId);
            
            if (!$allocation) {
                return [];
            }

            return [
                Forms\Components\Section::make('Edit Alokasi')
                    ->schema([
                        Forms\Components\Hidden::make('allocation_id')
                            ->default($allocation->id),

                        Forms\Components\Placeholder::make('category_info')
                            ->label('Kategori')
                            ->content($allocation->category_name),

                        Forms\Components\TextInput::make('allocated_amount')
                            ->label('Jumlah Alokasi')
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->required()
                            ->placeholder('0')
                            ->default($allocation->allocated_amount)
                            ->extraInputAttributes([
                                'oninput' => "
                                    let value = this.value.replace(/[^0-9]/g, '');
                                    if (value) {
                                        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                    }
                                "
                            ])
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : null)
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : ''),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->default($allocation->catatan)
                            ->rows(3),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Perubahan')
                            ->required()
                            ->rows(2)
                            ->placeholder('Jelaskan alasan perubahan untuk audit trail...'),
                    ])
            ];
        })
        ->action(function (array $data) {
            try {
                $allocation = BudgetAllocation::findOrFail($data['allocation_id']);
                $oldAmount = $allocation->allocated_amount;
                $newAmount = $data['allocated_amount'];
                $amountChanged = $newAmount - $oldAmount;

                // Validasi jika menambah
                if ($amountChanged > 0 && $amountChanged > $allocation->budgetPlan->remaining_budget) {
                    Notification::make()
                        ->title('Penambahan Melebihi Budget')
                        ->body('Sisa budget hanya Rp ' . number_format($allocation->budgetPlan->remaining_budget, 0, ',', '.'))
                        ->warning()
                        ->send();
                    return false;
                }

                $allocation->update([
                    'allocated_amount' => $newAmount,
                    'catatan' => $data['catatan']
                ]);

                $allocation->budgetPlan->updateTotals();

                // Log audit trail
                if (method_exists($allocation, 'logAudit')) {
                    $action = $amountChanged > 0 ? 'allocation_increased' : 'allocation_decreased';
                    $description = "Alokasi budget diubah dari Rp " . number_format($oldAmount, 0, ',', '.') . 
                                  " menjadi Rp " . number_format($newAmount, 0, ',', '.') . 
                                  " (" . ($amountChanged > 0 ? '+' : '') . "Rp " . number_format($amountChanged, 0, ',', '.') . ")";

                    $allocation->logAudit(
                        $action,
                        ['allocated_amount' => $oldAmount],
                        ['allocated_amount' => $newAmount],
                        $amountChanged,
                        $description,
                        $data['reason']
                    );
                }

                Notification::make()
                    ->title('Alokasi Berhasil Diperbarui')
                    ->body('Alokasi budget berhasil diubah')
                    ->success()
                    ->send();

                // Refresh halaman untuk update data
                return redirect(request()->header('Referer'));

            } catch (\Exception $e) {
                \Log::error('Error updating allocation: ' . $e->getMessage());
                
                Notification::make()
                    ->title('Terjadi Kesalahan')
                    ->body('Gagal memperbarui alokasi')
                    ->danger()
                    ->send();
                
                return false;
            }
        });
}


    public function deleteAllocationAction(): Actions\Action
{
    return Actions\Action::make('deleteAllocation')
        ->label('Hapus Alokasi')
        ->icon('heroicon-o-trash')
        ->color('danger')
        ->requiresConfirmation()
        ->modalHeading('Hapus Alokasi Budget')
        ->modalDescription('Apakah Anda yakin ingin menghapus alokasi ini? Tindakan ini tidak dapat dibatalkan.')
        ->form(function (array $arguments) {
            return [
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan Penghapusan')
                    ->required()
                    ->rows(3)
                    ->placeholder('Jelaskan alasan penghapusan untuk audit trail...'),
            ];
        })
        ->action(function (array $data, array $arguments) {
            try {
                $allocationId = $arguments['allocation'] ?? null;
                $allocation = BudgetAllocation::findOrFail($allocationId);
                
                // Check if allocation is being used
                if ($allocation->used_amount > 0) {
                    Notification::make()
                        ->title('Tidak Dapat Dihapus')
                        ->body('Alokasi yang sudah digunakan tidak dapat dihapus')
                        ->warning()
                        ->send();
                    return false;
                }

                $budgetPlan = $allocation->budgetPlan;
                $deletedAmount = $allocation->allocated_amount;
                $categoryName = $allocation->category_name;

                // Log audit before delete
                if (method_exists($allocation, 'logAudit')) {
                    $allocation->logAudit(
                        'allocation_deleted',
                        $allocation->toArray(),
                        null,
                        -$deletedAmount,
                        "Alokasi budget untuk {$categoryName} sebesar Rp " . number_format($deletedAmount, 0, ',', '.') . " telah dihapus",
                        $data['reason']
                    );
                }

                $allocation->delete();
                $budgetPlan->updateTotals();

                Notification::make()
                    ->title('Alokasi Berhasil Dihapus')
                    ->body('Alokasi budget telah dihapus')
                    ->success()
                    ->send();

                return redirect(request()->header('Referer'));

            } catch (\Exception $e) {
                \Log::error('Error deleting allocation: ' . $e->getMessage());
                
                Notification::make()
                    ->title('Terjadi Kesalahan')
                    ->body('Gagal menghapus alokasi')
                    ->danger()
                    ->send();
                
                return false;
            }
        });
}
}