<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanAnggaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'nomor_pengajuan',
        'judul_pengajuan',
        'deskripsi',
        'total_anggaran',
        'detail_items',
        'kategori',
        'tanggal_pengajuan',
        'tanggal_dibutuhkan',
        'justifikasi',
        'status',
        'budget_subcategory_id',
        'redaksi_approval_status',
        'redaksi_approved_by',
        'redaksi_approved_at',
        'redaksi_notes',
        'keuangan_approval_status',
        'keuangan_approved_by',
        'keuangan_approved_at',
        'keuangan_notes',
        'created_by',
        'realisasi_anggaran',
        'sisa_anggaran',
        'is_used',
    ];

    protected $casts = [
        'total_anggaran' => 'decimal:2',
        'realisasi_anggaran' => 'decimal:2',
        'sisa_anggaran' => 'decimal:2',
        'detail_items' => 'json',
        'tanggal_pengajuan' => 'date',
        'tanggal_dibutuhkan' => 'date',
        'redaksi_approved_at' => 'datetime',
        'keuangan_approved_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
        'redaksi_approval_status' => 'pending',
        'keuangan_approval_status' => 'pending',
        'realisasi_anggaran' => 0,
        'sisa_anggaran' => 0,
        'is_used' => false,
    ];

    // Relations
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function redaksiApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redaksi_approved_by');
    }

    public function keuanganApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'keuangan_approved_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'pengajuan_anggaran_id');
    }
    public function project(): BelongsTo
    {
    return $this->belongsTo(Project::class, 'project_id');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'pengajuan_anggaran_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved')
                    ->where('redaksi_approval_status', 'approved')
                    ->where('keuangan_approval_status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->approved()
                    ->where('is_used', false);
    }

    public function scopeNeedsRedaksiApproval($query)
    {
        return $query->where('redaksi_approval_status', 'pending')
                    ->where('status', 'pending');
    }

    public function scopeNeedsKeuanganApproval($query)
    {
        return $query->where('redaksi_approval_status', 'approved')
                    ->where('keuangan_approval_status', 'pending');
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getRedaksiStatusColorAttribute(): string
    {
        return match($this->redaksi_approval_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getKeuanganStatusColorAttribute(): string
    {
        return match($this->keuangan_approval_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getKategoriColorAttribute(): string
    {
        return match($this->kategori) {
            'project' => 'info',
            'operasional' => 'warning',
            'investasi' => 'success',
            'lainnya' => 'gray',
            default => 'gray',
        };
    }

    // Helper Methods
    public function canBeApprovedByRedaksi(): bool
    {
        return $this->redaksi_approval_status === 'pending' && $this->status === 'pending';
    }

    public function canBeApprovedByKeuangan(): bool
    {
        return $this->keuangan_approval_status === 'pending' && $this->redaksi_approval_status === 'approved';
    }

    public function isFullyApproved(): bool
    {
        return $this->status === 'approved' && 
               $this->redaksi_approval_status === 'approved' && 
               $this->keuangan_approval_status === 'approved';
    }

    public function isAvailable(): bool
    {
        return $this->isFullyApproved() && !$this->is_used;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nomor_pengajuan . ' - ' . $this->judul_pengajuan . ' (Rp ' . number_format($this->total_anggaran, 0, ',', '.') . ')';
    }

    public function calculateSisaAnggaran(): void
    {
        $this->sisa_anggaran = $this->total_anggaran - $this->realisasi_anggaran;
        $this->save();
    }

    public function markAsUsed(): void
    {
        $this->is_used = true;
        $this->save();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pengajuan) {
            if (empty($pengajuan->nomor_pengajuan)) {
                $pengajuan->nomor_pengajuan = $pengajuan->generateNomorPengajuan();
            }
            
            if (empty($pengajuan->tanggal_pengajuan)) {
                $pengajuan->tanggal_pengajuan = now();
            }
            
            $pengajuan->sisa_anggaran = $pengajuan->total_anggaran;
        });

        static::updating(function ($pengajuan) {
            // Auto calculate sisa anggaran
            if ($pengajuan->isDirty('total_anggaran') || $pengajuan->isDirty('realisasi_anggaran')) {
                $pengajuan->sisa_anggaran = $pengajuan->total_anggaran - $pengajuan->realisasi_anggaran;
            }
        });
    }

    public function generateNomorPengajuan(): string
    {
        $prefix = 'PA'; // Pengajuan Anggaran
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $counter = static::whereYear('tanggal_pengajuan', now()->year)
                         ->whereMonth('tanggal_pengajuan', now()->month)
                         ->count() + 1;
        
        return $prefix . '/' . $year . '/' . $month . '/' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }

    public function budgetSubcategory(): BelongsTo
    {
        return $this->belongsTo(BudgetSubcategory::class, 'budget_subcategory_id');
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class, 'budget_subcategory_id', 'budget_subcategory_id');
    }


    /**
 * Check apakah pengajuan ini bisa direalisasi
 */
public function canBeRealized(): bool
{
    return $this->status === 'approved' && !$this->isFullyRealized();
}

/**
 * Check apakah sudah full realized
 */
public function isFullyRealized(): bool
{
    $items = collect($this->detail_items);
    return $items->every(function ($item) {
        return isset($item['realisasi']) && $item['realisasi']['status'] === 'realized';
    });
}

/**
 * Get total realisasi dari semua items
 */
public function getTotalRealizedAttribute(): float
{
    return collect($this->detail_items)
        ->sum(function ($item) {
            return $item['realisasi']['total_actual'] ?? 0;
        });
}

/**
 * Get realisasi percentage
 */
public function getRealizationPercentageAttribute(): float
{
    if ($this->total_anggaran == 0) return 0;
    
    return round(($this->total_realized / $this->total_anggaran) * 100, 2);
}

/**
 * Get items yang belum direalisasi
 */
public function getUnrealizedItems(): array
{
    return collect($this->detail_items)
        ->filter(function ($item, $index) {
            $hasRealization = isset($item['realisasi']) && $item['realisasi']['status'] === 'realized';
            return !$hasRealization;
        })
        ->map(function ($item, $index) {
            $item['index'] = $index; // Tambah index untuk referensi
            return $item;
        })
        ->values()
        ->toArray();
}

/**
 * Update realisasi untuk item tertentu
 */
public function updateItemRealization(int $itemIndex, array $realizationData): bool
{
    $items = $this->detail_items;
    
    if (!isset($items[$itemIndex])) {
        return false;
    }
    
    // Tambahkan data realisasi ke item
    $items[$itemIndex]['realisasi'] = array_merge([
        'tanggal_realisasi' => now()->format('Y-m-d'),
        'realized_by' => auth()->id(),
        'realized_at' => now()->toISOString(),
        'status' => 'realized'
    ], $realizationData);
    
    // Update detail_items dan realisasi_anggaran
    $this->update([
        'detail_items' => $items,
        'realisasi_anggaran' => $this->calculateTotalRealized($items)
    ]);
    
    return true;
}

/**
 * Calculate total realized dari items
 */
private function calculateTotalRealized(array $items): float
{
    return collect($items)
        ->sum(function ($item) {
            return $item['realisasi']['total_actual'] ?? 0;
        });
}
}