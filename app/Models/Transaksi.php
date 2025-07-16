<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'workflow_type',
        'redaksi_approved_at',
        'redaksi_approved_by',
        'redaksi_notes',
        'pengajuan_anggaran_id',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'total_amount' => 'decimal:2',
        'attachments' => 'array',
        'approved_at' => 'datetime',
        'redaksi_approved_at' => 'datetime',
    ];

    protected $attributes = [
        'total_amount' => 0,
    ];

    // Existing Relations
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

    // New Relations for Workflow
    public function redaksiApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redaksi_approved_by');
    }

    public function pengajuanAnggaran(): BelongsTo
    {
        return $this->belongsTo(PengajuanAnggaran::class, 'pengajuan_anggaran_id');
    }

    // Existing Scopes
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

    // New Scopes for Workflow
    public function scopeProjectProposal($query)
    {
        return $query->where('workflow_type', 'project_proposal');
    }

    public function scopePendingRedaksi($query)
    {
        return $query->where('workflow_type', 'project_proposal')
                    ->where('status', 'draft');
    }

    public function scopePendingKeuangan($query)
    {
        return $query->where('workflow_type', 'project_proposal')
                    ->where('status', 'pending');
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

    // Helper Methods for Workflow
    public function isProjectProposal(): bool
    {
        return $this->workflow_type === 'project_proposal';
    }

    public function needsRedaksiApproval(): bool
    {
        return $this->isProjectProposal() && $this->status === 'draft';
    }

    public function needsKeuanganApproval(): bool
    {
        return $this->isProjectProposal() && $this->status === 'pending';
    }
}