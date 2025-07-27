<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasBudgetAuditTrail;

class BudgetAllocation extends Model
{
    use HasFactory, HasBudgetAuditTrail;

    protected $fillable = [
        'budget_plan_id',
        'budget_category_id',
        'budget_subcategory_id',
        'allocated_amount',
        'used_amount',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
    ];

    public function budgetPlan(): BelongsTo
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(BudgetSubcategory::class, 'budget_subcategory_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'budget_allocation_id');
    }

    // Method untuk mendapatkan sisa alokasi
    public function getRemainingAmountAttribute(): float
    {
        return $this->allocated_amount - $this->used_amount;
    }

    // Method untuk mendapatkan persentase penggunaan
    public function getUsagePercentageAttribute(): float
    {
        return $this->allocated_amount > 0 ? 
            round(($this->used_amount / $this->allocated_amount) * 100, 2) : 0;
    }

    // Method untuk mendapatkan nama lengkap kategori
    public function getCategoryNameAttribute(): string
    {
        if ($this->subcategory) {
            return $this->category->nama_kategori . ' - ' . $this->subcategory->nama_subkategori;
        }
        return $this->category->nama_kategori;
    }

    // Method untuk menggunakan budget (menambah used_amount)
    public function useBudget(float $amount, string $description = null): bool
    {
        if ($this->remaining_amount >= $amount) {
            $this->increment('used_amount', $amount);
            
            // Update total di budget plan
            $this->budgetPlan->updateTotals();
            
            return true;
        }
        
        return false;
    }

    // Method untuk mendapatkan status warna berdasarkan persentase penggunaan
    public function getUsageStatusColorAttribute(): string
    {
        $percentage = $this->usage_percentage;
        
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 75) return 'warning';
        if ($percentage >= 50) return 'info';
        return 'success';
    }

    // Method untuk cek apakah alokasi hampir habis
    public function isNearlyExhausted(float $threshold = 90): bool
    {
        return $this->usage_percentage >= $threshold;
    }

    // Boot method untuk auto-update budget plan totals
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($allocation) {
            $allocation->budgetPlan->updateTotals();
        });

        static::deleted(function ($allocation) {
            $allocation->budgetPlan->updateTotals();
        });
    }

    public function logAllocationIncrease(float $additionalAmount, string $reason = null): void
    {
        $this->logAudit(
            'allocation_added',
            ['allocated_amount' => $this->allocated_amount - $additionalAmount],
            ['allocated_amount' => $this->allocated_amount],
            $additionalAmount,
            "Alokasi ditambah sebesar Rp " . number_format($additionalAmount, 0, ',', '.'),
            $reason
        );
    }
}