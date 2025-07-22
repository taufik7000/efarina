<?php
// app/Filament/Widgets/SaldoAktualWidget.php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaldoAktualWidget extends BaseWidget
{
    // Refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';
    
    // Set order widget
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Hitung total pemasukan completed
        $totalPemasukan = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->sum('total_amount');

        // Hitung total pengeluaran completed  
        $totalPengeluaran = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->sum('total_amount');

        // Hitung saldo aktual
        $saldoAktual = $totalPemasukan - $totalPengeluaran;

        // Hitung transaksi pending (pending approval)
        $transaksiPending = Transaksi::where('status', 'pending')->count();

        // Hitung transaksi approved (menunggu pembayaran)
        $transaksiApproved = Transaksi::where('status', 'approved')->count();

        // Hitung persentase change pemasukan bulan ini vs bulan lalu
        $currentMonthIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereMonth('tanggal_transaksi', now()->month)
            ->whereYear('tanggal_transaksi', now()->year)
            ->sum('total_amount');

        $lastMonthIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereMonth('tanggal_transaksi', now()->subMonth()->month)
            ->whereYear('tanggal_transaksi', now()->subMonth()->year)
            ->sum('total_amount');

        $incomeChange = $lastMonthIncome > 0 
            ? (($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100 
            : 0;

        // Hitung persentase change pengeluaran bulan ini vs bulan lalu
        $currentMonthExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereMonth('tanggal_transaksi', now()->month)
            ->whereYear('tanggal_transaksi', now()->year)
            ->sum('total_amount');

        $lastMonthExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereMonth('tanggal_transaksi', now()->subMonth()->month)
            ->whereYear('tanggal_transaksi', now()->subMonth()->year)
            ->sum('total_amount');

        $expenseChange = $lastMonthExpense > 0 
            ? (($currentMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100 
            : 0;

        return [
            // Stat 1: Saldo Aktual (utama)
            Stat::make('Saldo Aktual', 'Rp ' . number_format($saldoAktual, 0, ',', '.'))
                ->description($saldoAktual >= 0 ? 'Saldo positif' : 'Saldo negatif - perlu perhatian')
                ->descriptionIcon($saldoAktual >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($saldoAktual >= 0 ? 'success' : 'danger')
                ->chart($this->getSaldoChart()),

            // Stat 2: Total Pemasukan
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPemasukan, 0, ',', '.'))
                ->description(
                    $incomeChange >= 0 
                        ? '+' . number_format($incomeChange, 1) . '% vs bulan lalu'
                        : number_format($incomeChange, 1) . '% vs bulan lalu'
                )
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($this->getPemasukanChart()),

            // Stat 3: Total Pengeluaran
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description(
                    $expenseChange >= 0 
                        ? '+' . number_format($expenseChange, 1) . '% vs bulan lalu'
                        : number_format($expenseChange, 1) . '% vs bulan lalu'
                )
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($this->getPengeluaranChart()),

            // Stat 4: Status Transaksi
            Stat::make('Menunggu Aksi', $transaksiPending + $transaksiApproved)
                ->description($transaksiPending . ' pending approval, ' . $transaksiApproved . ' menunggu pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color($transaksiPending + $transaksiApproved > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.transaksis.index', [
                    'tableFilters[status][values][0]' => 'pending',
                    'tableFilters[status][values][1]' => 'approved'
                ])),
        ];
    }

    /**
     * Chart untuk saldo 7 hari terakhir
     */
    protected function getSaldoChart(): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $pemasukan = Transaksi::where('jenis_transaksi', 'pemasukan')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', '<=', $date)
                ->sum('total_amount');
                
            $pengeluaran = Transaksi::where('jenis_transaksi', 'pengeluaran')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', '<=', $date)
                ->sum('total_amount');
                
            $data[] = $pemasukan - $pengeluaran;
        }
        
        return $data;
    }

    /**
     * Chart pemasukan 7 hari terakhir
     */
    protected function getPemasukanChart(): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $amount = Transaksi::where('jenis_transaksi', 'pemasukan')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', $date)
                ->sum('total_amount');
                
            $data[] = $amount;
        }
        
        return $data;
    }

    /**
     * Chart pengeluaran 7 hari terakhir
     */
    protected function getPengeluaranChart(): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $amount = Transaksi::where('jenis_transaksi', 'pengeluaran')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', $date)
                ->sum('total_amount');
                
            $data[] = $amount;
        }
        
        return $data;
    }
}