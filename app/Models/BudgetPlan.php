<?php
// app/Models/BudgetPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_period_id',
        'nama_budget',
        'total_budget',
        'total_allocated',
        'total_used',
        'status',
        'deskripsi',
        'approved_at',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'total_allocated' => 'decimal:2',
        'total_used' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(BudgetPeriod::class, 'budget_period_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    // Scope untuk budget yang sudah diapprove
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope untuk budget aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Method untuk mendapatkan sisa budget yang belum dialokasikan
    public function getRemainingBudgetAttribute(): float
    {
        return $this->total_budget - $this->total_allocated;
    }

    // Method untuk mendapatkan persentase alokasi
    public function getAllocationPercentageAttribute(): float
    {
        return $this->total_budget > 0 ? 
            round(($this->total_allocated / $this->total_budget) * 100, 2) : 0;
    }

    // Method untuk mendapatkan persentase penggunaan
    public function getUsagePercentageAttribute(): float
    {
        return $this->total_allocated > 0 ? 
            round(($this->total_used / $this->total_allocated) * 100, 2) : 0;
    }

    // Method untuk mendapatkan status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'approved' => 'warning',
            'active' => 'success',
            'closed' => 'danger',
            default => 'gray',
        };
    }

    // Method untuk update total allocated dan used
    public function updateTotals(): void
    {
        $this->update([
            'total_allocated' => $this->allocations()->sum('allocated_amount'),
            'total_used' => $this->allocations()->sum('used_amount'),
        ]);
    }

    // Method untuk approve budget
    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }
}