<?php
// app/Models/Transaksi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_transaksi',
        'jenis_transaksi',
        'tanggal_transaksi',
        'nama_transaksi',
        'deskripsi',
        'total_amount',
        'status',
        'metode_pembayaran',
        'nomor_referensi',
        'budget_allocation_id',
        'project_id',
        'attachments',
        'catatan_approval',
        'approved_at',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'total_amount' => 'decimal:2',
        'attachments' => 'array',
        'approved_at' => 'datetime',
    ];

    // Relasi
    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransaksiItem::class);
    }

    // Scopes
    public function scopePemasukan($query)
    {
        return $query->where('jenis_transaksi', 'pemasukan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('jenis_transaksi', 'pengeluaran');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_transaksi', now()->month)
                    ->whereYear('tanggal_transaksi', now()->year);
    }

    // Accessors & Mutators
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'completed' => 'success',
            default => 'gray',
        };
    }

    public function getJenisColorAttribute(): string
    {
        return match($this->jenis_transaksi) {
            'pemasukan' => 'success',
            'pengeluaran' => 'danger',
            default => 'gray',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        $prefix = $this->jenis_transaksi === 'pemasukan' ? '+' : '-';
        return $prefix . ' Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // Methods
    public function generateNomorTransaksi(): string
    {
        $prefix = match($this->jenis_transaksi) {
            'pemasukan' => 'IN',
            'pengeluaran' => 'OUT',
            default => 'TRX',
        };
        
        $date = $this->tanggal_transaksi->format('Ymd');
        $counter = static::whereDate('tanggal_transaksi', $this->tanggal_transaksi)
                         ->where('jenis_transaksi', $this->jenis_transaksi)
                         ->count() + 1;
        
        return $prefix . '/' . $date . '/' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }

    public function approve(int $userId, string $catatan = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'catatan_approval' => $catatan,
        ]);
    }

    public function complete(): void
    {
        if ($this->status === 'approved') {
            $this->update(['status' => 'completed']);
            
            // Update budget allocation jika pengeluaran
            if ($this->jenis_transaksi === 'pengeluaran' && $this->budget_allocation_id) {
                $this->budgetAllocation->increment('used_amount', $this->total_amount);
            }
        }
    }

    public function reject(int $userId, string $catatan): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'catatan_approval' => $catatan,
        ]);
    }

    // Update total amount dari items
    public function updateTotalFromItems(): void
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['total_amount' => $total]);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaksi) {
            if (empty($transaksi->nomor_transaksi)) {
                $transaksi->nomor_transaksi = $transaksi->generateNomorTransaksi();
            }
        });
    }
}