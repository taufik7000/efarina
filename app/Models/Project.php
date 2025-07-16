<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_project',
        'deskripsi',
        'pengajuan_anggaran_id',
        'project_manager_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'prioritas',
        'client_name',
        'client_contact',
        'budget_allocated',
        'budget_used',
        'progress_percentage',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'budget_allocated' => 'decimal:2',
        'budget_used' => 'decimal:2',
        'progress_percentage' => 'integer',
    ];

    protected $attributes = [
        'progress_percentage' => 0,
    ];

    // Relations
    public function pengajuanAnggaran(): BelongsTo
    {
        return $this->belongsTo(PengajuanAnggaran::class, 'pengajuan_anggaran_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    // Accessors for Status Colors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'planning' => 'warning',
            'active' => 'success',
            'on_hold' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    // Helper Methods
    public function hasApprovedBudget(): bool
    {
        return $this->pengajuanAnggaran && $this->pengajuanAnggaran->isFullyApproved();
    }

    public function getBudgetAmount(): float
    {
        return $this->pengajuanAnggaran ? $this->pengajuanAnggaran->total_anggaran : 0;
    }
}