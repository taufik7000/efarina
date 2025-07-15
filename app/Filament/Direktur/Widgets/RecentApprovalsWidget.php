<?php

namespace App\Filament\Direktur\Widgets;

use App\Models\Transaksi;
use App\Models\BudgetPlan;
use Filament\Widgets\Widget;

class RecentApprovalsWidget extends Widget
{
    protected static string $view = 'filament.direktur.widgets.recent-approvals';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        // Recent transactions needing approval
        $pendingTransactions = Transaksi::with(['createdBy', 'budgetAllocation.category'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Budget plans needing approval
        $pendingBudgetPlans = BudgetPlan::with(['createdBy', 'period'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return [
            'pendingTransactions' => $pendingTransactions,
            'pendingBudgetPlans' => $pendingBudgetPlans,
        ];
    }
}