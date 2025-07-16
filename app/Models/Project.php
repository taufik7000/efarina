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
        'project_manager_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'prioritas',
        'budget_allocated',
        'budget_used',
        'progress_percentage',
        'created_by',
        // Budget Proposal Fields
        'proposal_budget',
        'budget_items',
        'proposal_description',
        'redaksi_approval_status',
        'redaksi_approved_by',
        'redaksi_approved_at',
        'redaksi_notes',
        'keuangan_approval_status',
        'keuangan_approved_by',
        'keuangan_approved_at',
        'keuangan_notes',
    ];

    protected $attributes = [
        'redaksi_approval_status' => 'pending',
        'keuangan_approval_status' => 'pending',
        'progress_percentage' => 0,
    ];

    // Existing Relations
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

    // New Relations for Approval Workflow
    public function redaksiApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redaksi_approved_by');
    }

    public function keuanganApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'keuangan_approved_by');
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

    // Helper Methods
    public function isRedaksiApproved(): bool
    {
        return $this->redaksi_approval_status === 'approved';
    }

    public function isKeuanganApproved(): bool
    {
        return $this->keuangan_approval_status === 'approved';
    }

    public function isFullyApproved(): bool
    {
        return $this->isRedaksiApproved() && $this->isKeuanganApproved();
    }

    public function canBeApprovedByRedaksi(): bool
    {
        return $this->redaksi_approval_status === 'pending' && $this->proposal_budget > 0;
    }

    public function canBeApprovedByKeuangan(): bool
    {
        return $this->keuangan_approval_status === 'pending' && $this->isRedaksiApproved();
    }
}