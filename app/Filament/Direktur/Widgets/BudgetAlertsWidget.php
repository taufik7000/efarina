<?php

namespace App\Filament\Direktur\Widgets;

use App\Models\BudgetAllocation;
use App\Models\BudgetPlan;
use Filament\Widgets\Widget;

class BudgetAlertsWidget extends Widget
{
    protected static string $view = 'filament.direktur.widgets.budget-alerts';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        // Budget Over 90% Usage
        $highUsageBudgets = BudgetAllocation::with(['category', 'budgetPlan'])
            ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
            ->whereRaw('(used_amount / allocated_amount) * 100 >= 90')
            ->limit(5)
            ->get();

        // Budget Plans Pending Approval
        $pendingBudgetPlans = BudgetPlan::with(['period'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Overdue Transactions
        $overdueTransactions = \App\Models\Transaksi::with(['createdBy'])
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->limit(5)
            ->get();

        return [
            'highUsageBudgets' => $highUsageBudgets,
            'pendingBudgetPlans' => $pendingBudgetPlans,
            'overdueTransactions' => $overdueTransactions,
        ];
    }
}