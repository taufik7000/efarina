<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\TransaksiReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make()
            ->label('Buat Transaksi Baru')
            ->icon('heroicon-o-plus'),

        // Export Actions menggunakan Laravel Excel
        Actions\ActionGroup::make([
            Actions\Action::make('export_excel_bulan_ini')
                ->label('Export Excel Bulan Ini')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new TransaksiReportExport('bulan_ini'), 
                        'transaksi-' . now()->format('Y-m') . '.xlsx'
                    );
                }),

            Actions\Action::make('export_excel_tahun_ini')
                ->label('Export Excel Tahun Ini')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->action(function () {
                    return Excel::download(
                        new TransaksiReportExport('tahun_ini'), 
                        'transaksi-' . now()->year . '.xlsx'
                    );
                }),

            Actions\Action::make('export_pdf_bulan_ini')
                ->label('Export PDF Bulan Ini')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->action(function () {
                    return Excel::download(
                        new TransaksiReportExport('bulan_ini'), 
                        'transaksi-' . now()->format('Y-m') . '.pdf',
                        \Maatwebsite\Excel\Excel::DOMPDF
                    );
                }),

            Actions\Action::make('export_pdf_tahun_ini')
                ->label('Export PDF Tahun Ini')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    return Excel::download(
                        new TransaksiReportExport('tahun_ini'), 
                        'transaksi-' . now()->year . '.pdf',
                        \Maatwebsite\Excel\Excel::DOMPDF
                    );
                }),
        ])
        ->label('Export Laporan')
        ->icon('heroicon-o-arrow-down-tray')
        ->color('primary')
        ->button(),
    ];
}

    // Method untuk export PDF
    protected function exportTransaksiPDF(string $periode): \Symfony\Component\HttpFoundation\Response
    {
        $now = now();
        
        $query = \App\Models\Transaksi::with(['items', 'budgetAllocation.category', 'budgetAllocation.subcategory', 'createdBy'])
            ->where('status', 'completed');
        
        if ($periode === 'bulan_ini') {
            $query->whereMonth('tanggal_transaksi', $now->month)
                  ->whereYear('tanggal_transaksi', $now->year);
            $judulPeriode = $now->format('F Y');
            $filename = 'laporan-transaksi-' . $now->format('Y-m') . '.pdf';
        } else {
            $query->whereYear('tanggal_transaksi', $now->year);
            $judulPeriode = 'Tahun ' . $now->year;
            $filename = 'laporan-transaksi-' . $now->year . '.pdf';
        }
        
        $transaksis = $query->orderBy('tanggal_transaksi', 'desc')->get();
        
        // Hitung ringkasan
        $totalPemasukan = $transaksis->where('jenis_transaksi', 'pemasukan')->sum('total_amount');
        $totalPengeluaran = $transaksis->where('jenis_transaksi', 'pengeluaran')->sum('total_amount');
        $saldoBersih = $totalPemasukan - $totalPengeluaran;
        
        // Statistik tambahan
        $jumlahTransaksi = $transaksis->count();
        $transaksiPemasukan = $transaksis->where('jenis_transaksi', 'pemasukan')->count();
        $transaksiPengeluaran = $transaksis->where('jenis_transaksi', 'pengeluaran')->count();
        $transaksiDenganBudget = $transaksis->whereNotNull('budget_allocation_id')->count();
        $transaksiDiluarBudget = $transaksis->whereNull('budget_allocation_id')
                                           ->where('jenis_transaksi', 'pengeluaran')
                                           ->count();
        
        // Generate HTML content
        $html = view('exports.transaksi-pdf', compact(
            'transaksis',
            'judulPeriode',
            'totalPemasukan',
            'totalPengeluaran', 
            'saldoBersih',
            'jumlahTransaksi',
            'transaksiPemasukan',
            'transaksiPengeluaran',
            'transaksiDenganBudget',
            'transaksiDiluarBudget',
            'now'
        ))->render();
        
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    // Method untuk menampilkan widgets di atas table
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SaldoAktualWidget::class,
        ];
    }

    // Customize widget grid layout
    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2, 
            'lg' => 4,
        ];
    }

    // Method untuk tabs
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-queue-list')
                ->badge($this->getModel()::count()),
                
            'draft' => Tab::make('Draft')
                ->icon('heroicon-o-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge($this->getModel()::where('status', 'draft')->count()),
                
            'pending' => Tab::make('Menunggu Approval')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'approved' => Tab::make('Menunggu Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge($this->getModel()::where('status', 'approved')->count())
                ->badgeColor('info'),
                
            'completed' => Tab::make('Selesai')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge($this->getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),
                
            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge($this->getModel()::where('status', 'rejected')->count())
                ->badgeColor('danger'),

            // Tab untuk pengeluaran diluar budget
            'outside_budget' => Tab::make('Diluar Budget Plan')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenis_transaksi', 'pengeluaran')
                                                               ->whereNull('budget_allocation_id'))
                ->badge($this->getModel()::where('jenis_transaksi', 'pengeluaran')
                                       ->whereNull('budget_allocation_id')
                                       ->count())
                ->badgeColor('warning'),
        ];
    }
}