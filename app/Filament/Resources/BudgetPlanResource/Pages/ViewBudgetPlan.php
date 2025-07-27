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

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn() => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),

            Actions\Action::make('create_allocation')
                ->label('Tambah Alokasi')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn() => auth()->user()->hasRole(['keuangan', 'direktur']))
                ->url(fn() => BudgetAllocationResource::getUrl('create', [
                    'budget_plan_id' => $this->getRecord()->id
                ])),

            Actions\Action::make('export_report')
                ->label('Export Laporan')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    // Logic untuk export report
                    return response()->streamDownload(function () {
                        echo $this->generateBudgetReport();
                    }, 'budget-plan-' . $this->getRecord()->id . '.pdf');
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
                                        ->state(fn($record) => $record->allocations()->with(['category', 'subcategory'])->get())
                                ])
                                ->collapsible(),

                            // Detail Transaksi Budget Plan
                            Infolists\Components\Section::make('Transaksi Terkait')
                                ->schema([
                                    Infolists\Components\ViewEntry::make('transactions_table')
                                        ->label('')
                                        ->view('filament.components.budget-transactions-table')
                                        ->state(fn($record) => $this->getBudgetTransactions($record))
                                ])
                                ->collapsible()
                                ->collapsed(),
                        ])
                            ->columnSpan(2), // Mengambil 2 kolom dari 3 (lebih lebar)

                        // Right Column - Info & Summary
                        Infolists\Components\Group::make([
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
                                        ->color(fn(string $state): string => match ($state) {
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
                                                ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                                                ->weight(FontWeight::SemiBold),
                                        ]),

                                    Infolists\Components\Fieldset::make('Persentase')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('allocation_percentage')
                                                ->label('% Alokasi')
                                                ->suffix('%')
                                                ->color(fn($state) => $state >= 100 ? 'danger' : ($state >= 80 ? 'warning' : 'success'))
                                                ->badge(),

                                            Infolists\Components\TextEntry::make('usage_percentage')
                                                ->label('% Penggunaan')
                                                ->suffix('%')
                                                ->color(fn($state) => $state >= 90 ? 'danger' : ($state >= 75 ? 'warning' : 'success'))
                                                ->badge(),
                                        ]),
                                ])
                                ->compact(),

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
                                ->compact()
                                ->collapsible()
                                ->collapsed(),

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
                                ->visible(fn($record) => $record->approved_at || $record->approved_by),
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
                ->whereHas('budgetAllocation', function ($query) use ($record) {
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
}