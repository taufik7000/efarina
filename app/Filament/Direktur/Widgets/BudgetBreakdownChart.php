<?php

namespace App\Filament\Direktur\Widgets;

use App\Models\BudgetAllocation;
use Filament\Widgets\ChartWidget;

class BudgetBreakdownChart extends ChartWidget
{
    protected static ?string $heading = 'Breakdown Budget per Kategori';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $allocations = BudgetAllocation::with(['category'])
            ->whereHas('budgetPlan', function($q) {
                $q->where('status', 'active');
            })
            ->selectRaw('budget_category_id, SUM(allocated_amount) as total_allocated, SUM(used_amount) as total_used')
            ->groupBy('budget_category_id')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Budget Dialokasikan',
                    'data' => $allocations->pluck('total_allocated')->toArray(),
                    'backgroundColor' => '#3B82F6',
                ],
                [
                    'label' => 'Budget Terpakai',
                    'data' => $allocations->pluck('total_used')->toArray(),
                    'backgroundColor' => '#EF4444',
                ],
            ],
            'labels' => $allocations->pluck('category.nama_kategori')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}