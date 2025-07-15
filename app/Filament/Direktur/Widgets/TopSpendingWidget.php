<?php

namespace App\Filament\Direktur\Widgets;

use App\Models\Transaksi;
use App\Models\BudgetAllocation;
use Filament\Widgets\Widget;

class TopSpendingWidget extends Widget
{
    protected static string $view = 'filament.direktur.widgets.top-spending';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Top spending categories this month
        $topSpending = Transaksi::with(['budgetAllocation.category'])
            ->where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereMonth('tanggal_transaksi', $currentMonth)
            ->whereYear('tanggal_transaksi', $currentYear)
            ->whereNotNull('budget_allocation_id')
            ->selectRaw('budget_allocation_id, SUM(total_amount) as total_spent')
            ->groupBy('budget_allocation_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        return [
            'topSpending' => $topSpending,
        ];
    }
}