<?php
// app/Models/BudgetAuditTrail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BudgetAuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id', 
        'action',
        'old_values',
        'new_values',
        'amount_changed',
        'description',
        'reason',
        'user_id',
        'user_name',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'amount_changed' => 'decimal:2',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk format action yang readable
    public function getFormattedActionAttribute(): string
    {
        return match($this->action) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            'budget_increased' => 'Total Budget Ditambah',
            'budget_decreased' => 'Total Budget Dikurangi',
            'allocation_added' => 'Alokasi Ditambah',
            'allocation_updated' => 'Alokasi Diperbarui',
            'allocation_deleted' => 'Alokasi Dihapus',
            'status_changed' => 'Status Diubah',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->action),
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        if (!$this->amount_changed) return '-';
        
        $prefix = $this->amount_changed > 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format($this->amount_changed, 0, ',', '.');
    }
}