<?php
// app/Filament/Direktur/Widgets/FinancialOverviewWidget.php

namespace App\Filament\Direktur\Widgets;

use App\Models\BudgetPlan;
use App\Models\BudgetAllocation;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    // Filter properties
    public array $filters = [];

    public function mount(array $filters = []): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        // Get date range dari filter atau default
        $dateRange = $this->getDateRange();
        $budgetPeriod = $this->getBudgetPeriod();
        
        // Data untuk comparison (periode sebelumnya)
        $previousDateRange = $this->getPreviousDateRange($dateRange);

        return [
            $this->getTotalActiveBudgetStat($budgetPeriod),
            $this->getMonthlyExpenseStat($dateRange, $previousDateRange),
            $this->getMonthlyIncomeStat($dateRange, $previousDateRange),
            $this->getPendingApprovalStat(),
            $this->getBudgetUtilizationStat($budgetPeriod),
            $this->getCashFlowStat($dateRange, $previousDateRange),
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function getDateRange(): array
    {
        return [
            'from' => isset($this->filters['date_from']) 
                ? Carbon::parse($this->filters['date_from']) 
                : now()->startOfMonth(),
            'to' => isset($this->filters['date_to']) 
                ? Carbon::parse($this->filters['date_to']) 
                : now(),
        ];
    }

    protected function getBudgetPeriod(): ?int
    {
        return $this->filters['budget_period'] ?? null;
    }

    protected function getPreviousDateRange(array $currentRange): array
    {
        $daysDiff = Carbon::parse($currentRange['from'])->diffInDays(Carbon::parse($currentRange['to']));
        
        return [
            'from' => Carbon::parse($currentRange['from'])->subDays($daysDiff + 1),
            'to' => Carbon::parse($currentRange['from'])->subDay(),
        ];
    }

    // ========================================
    // STAT METHODS
    // ========================================

    protected function getTotalActiveBudgetStat(?int $budgetPeriod): Stat
    {
        $query = BudgetPlan::where('status', 'active');
        
        if ($budgetPeriod) {
            $query->where('budget_period_id', $budgetPeriod);
        } else {
            // Default ke tahun current
            $query->whereHas('period', function($q) {
                $q->whereYear('tanggal_mulai', now()->year);
            });
        }

        $activeBudget = $query->sum('total_budget');
        $budgetCount = $query->count();

        // Hitung total yang sudah dialokasikan
        $totalAllocated = $query->sum('total_allocated');
        $allocationPercentage = $activeBudget > 0 ? ($totalAllocated / $activeBudget) * 100 : 0;

        return Stat::make('Total Budget Aktif', 'Rp ' . number_format($activeBudget, 0, ',', '.'))
            ->description($budgetCount . ' budget plan aktif • ' . number_format($allocationPercentage, 1) . '% dialokasikan')
            ->descriptionIcon('heroicon-m-currency-dollar')
            ->color('success')
            ->chart($this->getBudgetTrendChart($budgetPeriod))
            ->url(route('filament.direktur.resources.budget-plans.index', [
                'tableFilters[status][value]' => 'active'
            ]));
    }

    protected function getMonthlyExpenseStat(array $dateRange, array $previousDateRange): Stat
    {
        // Current period expense
        $currentExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->sum('total_amount');

        // Previous period expense untuk comparison
        $previousExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$previousDateRange['from'], $previousDateRange['to']])
            ->sum('total_amount');

        // Calculate percentage change
        $percentageChange = $this->calculatePercentageChange($previousExpense, $currentExpense);
        
        // Count transaksi
        $transactionCount = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->count();

        $description = $transactionCount . ' transaksi';
        if ($percentageChange !== null) {
            $description .= ' • ' . ($percentageChange >= 0 ? '+' : '') . number_format($percentageChange, 1) . '% vs periode sebelumnya';
        }

        return Stat::make(
            'Pengeluaran ' . $this->getDateRangeLabel($dateRange), 
            'Rp ' . number_format($currentExpense, 0, ',', '.')
        )
            ->description($description)
            ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($percentageChange >= 0 ? 'danger' : 'success')
            ->chart($this->getExpenseChart($dateRange))
            ->url(route('filament.direktur.resources.transaksis.index', [
                'tableFilters[jenis_transaksi][value]' => 'pengeluaran',
                'tableFilters[status][value]' => 'completed'
            ]));
    }

    protected function getMonthlyIncomeStat(array $dateRange, array $previousDateRange): Stat
    {
        // Current period income
        $currentIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->sum('total_amount');

        // Previous period income untuk comparison
        $previousIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$previousDateRange['from'], $previousDateRange['to']])
            ->sum('total_amount');

        // Calculate percentage change
        $percentageChange = $this->calculatePercentageChange($previousIncome, $currentIncome);
        
        // Count transaksi
        $transactionCount = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->count();

        $description = $transactionCount . ' transaksi';
        if ($percentageChange !== null) {
            $description .= ' • ' . ($percentageChange >= 0 ? '+' : '') . number_format($percentageChange, 1) . '% vs periode sebelumnya';
        }

        return Stat::make(
            'Pemasukan ' . $this->getDateRangeLabel($dateRange), 
            'Rp ' . number_format($currentIncome, 0, ',', '.')
        )
            ->description($description)
            ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($percentageChange >= 0 ? 'success' : 'danger')
            ->chart($this->getIncomeChart($dateRange))
            ->url(route('filament.direktur.resources.transaksis.index', [
                'tableFilters[jenis_transaksi][value]' => 'pemasukan',
                'tableFilters[status][value]' => 'completed'
            ]));
    }

    protected function getPendingApprovalStat(): Stat
    {
        $pendingTransactions = Transaksi::where('status', 'pending')->count();
        $pendingBudgetPlans = BudgetPlan::where('status', 'draft')->count();
        $totalPending = $pendingTransactions + $pendingBudgetPlans;

        // Hitung berapa yang urgent (> 3 hari)
        $urgentTransactions = Transaksi::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        $description = $pendingTransactions . ' transaksi, ' . $pendingBudgetPlans . ' budget plan';
        if ($urgentTransactions > 0) {
            $description .= ' • ' . $urgentTransactions . ' urgent (>3 hari)';
        }

        $color = 'success';
        if ($totalPending > 10) $color = 'danger';
        elseif ($totalPending > 5) $color = 'warning';

        return Stat::make('Pending Approval', $totalPending)
            ->description($description)
            ->descriptionIcon('heroicon-m-clock')
            ->color($color)
            ->chart($this->getPendingTrendChart())
            ->url(route('filament.direktur.resources.transaksis.index', [
                'tableFilters[status][value]' => 'pending'
            ]));
    }

    protected function getBudgetUtilizationStat(?int $budgetPeriod): Stat
    {
        $query = BudgetAllocation::whereHas('budgetPlan', function($q) use ($budgetPeriod) {
            $q->where('status', 'active');
            if ($budgetPeriod) {
                $q->where('budget_period_id', $budgetPeriod);
            }
        });

        $totalAllocated = $query->sum('allocated_amount');
        $totalUsed = $query->sum('used_amount');
        
        $utilizationRate = $totalAllocated > 0 ? ($totalUsed / $totalAllocated) * 100 : 0;

        // Hitung berapa kategori yang over budget
        $overBudgetCount = $query->whereRaw('used_amount > allocated_amount')->count();
        $highUsageCount = $query->whereRaw('(used_amount / allocated_amount) * 100 >= 90')->count();

        $description = 'dari Rp ' . number_format($totalAllocated, 0, ',', '.') . ' yang dialokasikan';
        if ($overBudgetCount > 0) {
            $description .= ' • ' . $overBudgetCount . ' kategori over budget';
        } elseif ($highUsageCount > 0) {
            $description .= ' • ' . $highUsageCount . ' kategori hampir habis';
        }

        $color = 'success';
        if ($utilizationRate >= 95) $color = 'danger';
        elseif ($utilizationRate >= 85) $color = 'warning';
        elseif ($utilizationRate >= 75) $color = 'info';

        return Stat::make('Utilisasi Budget', number_format($utilizationRate, 1) . '%')
            ->description($description)
            ->descriptionIcon('heroicon-m-chart-pie')
            ->color($color)
            ->chart($this->getUtilizationChart($budgetPeriod))
            ->url(route('filament.direktur.resources.budget-allocations.index'));
    }

    protected function getCashFlowStat(array $dateRange, array $previousDateRange): Stat
    {
        // Current period cash flow
        $currentIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->sum('total_amount');

        $currentExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$dateRange['from'], $dateRange['to']])
            ->sum('total_amount');

        $currentCashFlow = $currentIncome - $currentExpense;

        // Previous period cash flow
        $previousIncome = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$previousDateRange['from'], $previousDateRange['to']])
            ->sum('total_amount');

        $previousExpense = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$previousDateRange['from'], $previousDateRange['to']])
            ->sum('total_amount');

        $previousCashFlow = $previousIncome - $previousExpense;

        // Calculate percentage change
        $percentageChange = $this->calculatePercentageChange($previousCashFlow, $currentCashFlow);

        $description = 'Pemasukan: Rp ' . number_format($currentIncome, 0, ',', '.') . 
                      ' - Pengeluaran: Rp ' . number_format($currentExpense, 0, ',', '.');
        
        if ($percentageChange !== null) {
            $description .= ' • ' . ($percentageChange >= 0 ? '+' : '') . number_format($percentageChange, 1) . '% vs periode sebelumnya';
        }

        $color = $currentCashFlow >= 0 ? 'success' : 'danger';

        return Stat::make(
            'Net Cash Flow ' . $this->getDateRangeLabel($dateRange),
            ($currentCashFlow >= 0 ? 'Rp ' : '-Rp ') . number_format(abs($currentCashFlow), 0, ',', '.')
        )
            ->description($description)
            ->descriptionIcon($currentCashFlow >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($color)
            ->chart($this->getCashFlowChart($dateRange));
    }

    // ========================================
    // CHART METHODS
    // ========================================

    protected function getBudgetTrendChart(?int $budgetPeriod): array
    {
        // Get budget trend for last 6 months
        $months = collect(range(5, 0))->map(function ($monthsBack) {
            return now()->subMonths($monthsBack);
        });

        $data = [];
        foreach ($months as $month) {
            $budget = BudgetPlan::where('status', 'active')
                ->whereHas('period', function($q) use ($month) {
                    $q->whereMonth('tanggal_mulai', $month->month)
                      ->whereYear('tanggal_mulai', $month->year);
                })
                ->sum('total_budget');
            
            $data[] = $budget / 1000000; // Convert to millions
        }

        return $data;
    }

    protected function getExpenseChart(array $dateRange): array
    {
        // Daily expense for the period
        $days = Carbon::parse($dateRange['from'])->diffInDays(Carbon::parse($dateRange['to'])) + 1;
        
        if ($days <= 7) {
            // Daily breakdown
            return $this->getDailyExpenseData($dateRange);
        } elseif ($days <= 31) {
            // Weekly breakdown
            return $this->getWeeklyExpenseData($dateRange);
        } else {
            // Monthly breakdown
            return $this->getMonthlyExpenseData($dateRange);
        }
    }

    protected function getIncomeChart(array $dateRange): array
    {
        // Similar logic to expense chart
        $days = Carbon::parse($dateRange['from'])->diffInDays(Carbon::parse($dateRange['to'])) + 1;
        
        if ($days <= 7) {
            return $this->getDailyIncomeData($dateRange);
        } elseif ($days <= 31) {
            return $this->getWeeklyIncomeData($dateRange);
        } else {
            return $this->getMonthlyIncomeData($dateRange);
        }
    }

    protected function getPendingTrendChart(): array
    {
        // Last 7 days pending count
        $days = collect(range(6, 0))->map(function ($daysBack) {
            return now()->subDays($daysBack)->toDateString();
        });

        $data = [];
        foreach ($days as $day) {
            $pendingCount = Transaksi::where('status', 'pending')
                ->whereDate('created_at', $day)
                ->count();
            $data[] = $pendingCount;
        }

        return $data;
    }

    protected function getUtilizationChart(?int $budgetPeriod): array
    {
        // Utilization by category
        $allocations = BudgetAllocation::with('category')
            ->whereHas('budgetPlan', function($q) use ($budgetPeriod) {
                $q->where('status', 'active');
                if ($budgetPeriod) {
                    $q->where('budget_period_id', $budgetPeriod);
                }
            })
            ->selectRaw('budget_category_id, SUM(allocated_amount) as total_allocated, SUM(used_amount) as total_used')
            ->groupBy('budget_category_id')
            ->get();

        $data = [];
        foreach ($allocations->take(5) as $allocation) {
            $utilizationRate = $allocation->total_allocated > 0 
                ? ($allocation->total_used / $allocation->total_allocated) * 100 
                : 0;
            $data[] = $utilizationRate;
        }

        return $data;
    }

    protected function getCashFlowChart(array $dateRange): array
    {
        // Weekly cash flow for the period
        $weeks = [];
        $start = Carbon::parse($dateRange['from'])->startOfWeek();
        $end = Carbon::parse($dateRange['to'])->endOfWeek();

        while ($start->lte($end)) {
            $weekStart = $start->copy();
            $weekEnd = $start->copy()->endOfWeek();
            
            $income = Transaksi::where('jenis_transaksi', 'pemasukan')
                ->where('status', 'completed')
                ->whereBetween('tanggal_transaksi', [$weekStart, $weekEnd])
                ->sum('total_amount');

            $expense = Transaksi::where('jenis_transaksi', 'pengeluaran')
                ->where('status', 'completed')
                ->whereBetween('tanggal_transaksi', [$weekStart, $weekEnd])
                ->sum('total_amount');

            $weeks[] = ($income - $expense) / 1000000; // Convert to millions
            $start->addWeek();
        }

        return $weeks;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    protected function calculatePercentageChange(?float $previous, float $current): ?float
    {
        if ($previous === null || $previous == 0) {
            return null;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

    protected function getDateRangeLabel(array $dateRange): string
    {
        $from = Carbon::parse($dateRange['from']);
        $to = Carbon::parse($dateRange['to']);

        if ($from->isSameDay($to)) {
            return $from->format('d M Y');
        }

        if ($from->isSameMonth($to)) {
            return $from->format('M Y');
        }

        return $from->format('M Y') . ' - ' . $to->format('M Y');
    }

    // ========================================
    // DETAILED CHART DATA METHODS
    // ========================================

    protected function getDailyExpenseData(array $dateRange): array
    {
        $data = [];
        $current = Carbon::parse($dateRange['from']);
        $end = Carbon::parse($dateRange['to']);

        while ($current->lte($end)) {
            $expense = Transaksi::where('jenis_transaksi', 'pengeluaran')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', $current)
                ->sum('total_amount');
            
            $data[] = $expense / 1000000;
            $current->addDay();
        }

        return $data;
    }

    protected function getDailyIncomeData(array $dateRange): array
    {
        $data = [];
        $current = Carbon::parse($dateRange['from']);
        $end = Carbon::parse($dateRange['to']);

        while ($current->lte($end)) {
            $income = Transaksi::where('jenis_transaksi', 'pemasukan')
                ->where('status', 'completed')
                ->whereDate('tanggal_transaksi', $current)
                ->sum('total_amount');
            
            $data[] = $income / 1000000;
            $current->addDay();
        }

        return $data;
    }

    protected function getWeeklyExpenseData(array $dateRange): array
    {
        // Implementation for weekly data
        return [];
    }

    protected function getWeeklyIncomeData(array $dateRange): array
    {
        // Implementation for weekly data  
        return [];
    }

    protected function getMonthlyExpenseData(array $dateRange): array
    {
        // Implementation for monthly data
        return [];
    }

    protected function getMonthlyIncomeData(array $dateRange): array
    {
        // Implementation for monthly data
        return [];
    }
}