<?php

namespace App\Filament\Direktur\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\ChartWidget;

class CashFlowTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Cash Flow (6 Bulan Terakhir)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($monthsBack) {
            return now()->subMonths($monthsBack);
        });

        $incomeData = [];
        $expenseData = [];
        $labels = [];

        foreach ($months as $month) {
            $income = Transaksi::where('jenis_transaksi', 'pemasukan')
                ->where('status', 'completed')
                ->whereMonth('tanggal_transaksi', $month->month)
                ->whereYear('tanggal_transaksi', $month->year)
                ->sum('total_amount');

            $expense = Transaksi::where('jenis_transaksi', 'pengeluaran')
                ->where('status', 'completed')
                ->whereMonth('tanggal_transaksi', $month->month)
                ->whereYear('tanggal_transaksi', $month->year)
                ->sum('total_amount');

            $incomeData[] = $income;
            $expenseData[] = $expense;
            $labels[] = $month->format('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}