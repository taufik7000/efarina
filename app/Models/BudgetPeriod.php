<?php
// app/Models/BudgetPeriod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BudgetPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_periode',
        'type',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function budgetPlans(): HasMany
    {
        return $this->hasMany(BudgetPlan::class);
    }

    // Scope untuk periode aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope untuk periode yang sedang berjalan
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('tanggal_mulai', '<=', $today)
                    ->where('tanggal_selesai', '>=', $today);
    }

    // Method untuk cek apakah periode sedang berjalan
    public function isCurrentPeriod(): bool
    {
        $today = Carbon::today();
        return $this->tanggal_mulai <= $today && $this->tanggal_selesai >= $today;
    }

    // Method untuk mendapatkan status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'active' => 'success',
            'closed' => 'danger',
            default => 'gray',
        };
    }

    // Method untuk mendapatkan type badge color
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'monthly' => 'info',
            'quarterly' => 'warning',
            'yearly' => 'primary',
            default => 'gray',
        };
    }
}