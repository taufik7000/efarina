<?php

namespace App\Console\Commands;

use App\Models\Transaksi;
use App\Models\BudgetAllocation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBudgetAllocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'budget:sync-allocation 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Force update even if budget exceeds}';

    /**
     * The console command description.
     */
    protected $description = 'Sync budget allocation with existing completed transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🔄 Starting Budget Allocation Sync...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Reset all budget allocations used_amount to 0 first
        if (!$isDryRun) {
            $this->info('📊 Resetting all budget allocations...');
            BudgetAllocation::query()->update(['used_amount' => 0]);
        }

        // Get all completed pengeluaran transactions with budget allocation
        $transactions = Transaksi::where('status', 'completed')
            ->where('jenis_transaksi', 'pengeluaran')
            ->whereNotNull('budget_allocation_id')
            ->with(['budgetAllocation.category', 'budgetAllocation.subcategory'])
            ->orderBy('created_at')
            ->get();

        $this->info("📋 Found {$transactions->count()} completed expense transactions with budget allocation");
        $this->newLine();

        if ($transactions->isEmpty()) {
            $this->info('✅ No transactions to process');
            return 0;
        }

        $processed = 0;
        $errors = 0;
        $warnings = 0;
        $totalAmount = 0;

        // Group by budget allocation for summary
        $budgetGroups = $transactions->groupBy('budget_allocation_id');

        $this->info('📊 Processing by Budget Allocation:');
        $this->newLine();

        foreach ($budgetGroups as $budgetId => $transactionGroup) {
            $budgetAllocation = $transactionGroup->first()->budgetAllocation;
            $groupTotal = $transactionGroup->sum('total_amount');
            
            if (!$budgetAllocation) {
                $this->error("❌ Budget Allocation ID {$budgetId} not found");
                $errors++;
                continue;
            }

            $categoryName = $budgetAllocation->category_name;
            $allocatedAmount = $budgetAllocation->allocated_amount;
            $currentUsed = $budgetAllocation->used_amount;

            $this->line("📂 {$categoryName}");
            $this->line("   Allocated: Rp " . number_format($allocatedAmount, 0, ',', '.'));
            $this->line("   Current Used: Rp " . number_format($currentUsed, 0, ',', '.'));
            $this->line("   To Add: Rp " . number_format($groupTotal, 0, ',', '.'));
            $this->line("   Transactions: {$transactionGroup->count()}");

            // Check if adding this would exceed budget
            $newUsedAmount = $currentUsed + $groupTotal;
            if ($newUsedAmount > $allocatedAmount && !$isForce) {
                $exceeds = $newUsedAmount - $allocatedAmount;
                $this->warn("   ⚠️  WARNING: Would exceed budget by Rp " . number_format($exceeds, 0, ',', '.'));
                $this->warn("   Use --force to override this check");
                $warnings++;
                $this->newLine();
                continue;
            }

            if (!$isDryRun) {
                // Update budget allocation
                $budgetAllocation->increment('used_amount', $groupTotal);
                
                // Update budget plan totals
                $budgetAllocation->budgetPlan->updateTotals();
                
                $this->info("   ✅ Updated successfully");
            } else {
                $this->info("   🔍 Would update (dry run)");
            }

            $processed++;
            $totalAmount += $groupTotal;

            // Show transaction details if verbose
            if ($this->output->isVerbose()) {
                $this->line("   Transactions:");
                foreach ($transactionGroup as $transaction) {
                    $this->line("     - #{$transaction->nomor_transaksi}: Rp " . 
                               number_format($transaction->total_amount, 0, ',', '.') . 
                               " ({$transaction->nama_transaksi})");
                }
            }

            $this->newLine();
        }

        // Summary
        $this->info('📊 SUMMARY:');
        $this->line("   Budget Allocations Processed: {$processed}");
        $this->line("   Total Transactions: {$transactions->count()}");
        $this->line("   Total Amount: Rp " . number_format($totalAmount, 0, ',', '.'));
        
        if ($warnings > 0) {
            $this->warn("   Warnings (Budget Exceeds): {$warnings}");
        }
        
        if ($errors > 0) {
            $this->error("   Errors: {$errors}");
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info('🔍 This was a dry run. Run without --dry-run to apply changes.');
            $this->info('💡 Use --force to override budget limit warnings.');
        } else {
            $this->info('✅ Budget allocation sync completed successfully!');
        }

        return 0;
    }

    /**
     * Show detailed budget allocation report
     */
    private function showDetailedReport()
    {
        $this->newLine();
        $this->info('📋 DETAILED BUDGET ALLOCATION REPORT:');
        $this->newLine();

        $allocations = BudgetAllocation::with(['category', 'subcategory', 'transaksis' => function($query) {
            $query->where('status', 'completed')->where('jenis_transaksi', 'pengeluaran');
        }])->get();

        $table = [];
        foreach ($allocations as $allocation) {
            $transactionCount = $allocation->transaksis->count();
            $usagePercentage = $allocation->usage_percentage;
            
            $table[] = [
                $allocation->category_name,
                'Rp ' . number_format($allocation->allocated_amount, 0, ',', '.'),
                'Rp ' . number_format($allocation->used_amount, 0, ',', '.'),
                'Rp ' . number_format($allocation->remaining_amount, 0, ',', '.'),
                $usagePercentage . '%',
                $transactionCount
            ];
        }

        $this->table([
            'Category',
            'Allocated',
            'Used',
            'Remaining',
            'Usage %',
            'Transactions'
        ], $table);
    }
}