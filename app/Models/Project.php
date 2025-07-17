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
        'tujuan_utama',
        'target_audience',
        'target_metrics',
        'deliverables',
        'expected_outcomes',
        'pengajuan_anggaran_id',
        'project_manager_id',
        'tanggal_mulai',
        'tanggal_deadline',
        'tanggal_selesai',
        'status',
        'prioritas',
        'client_name',
        'client_contact',
        'budget',
        'progress_percentage',
        'team_members',
        'created_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'tanggal_selesai' => 'date',
        'budget' => 'decimal:2',
        'progress_percentage' => 'integer',
        'target_metrics' => 'array',
        'deliverables' => 'array',
        'team_members' => 'array',
    ];

    protected $attributes = [
        'progress_percentage' => 0,
        'status' => 'draft',
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

    // Helper methods untuk team members
    public function getTeamMemberUsers()
    {
        if (!$this->team_members) {
            return collect();
        }
        
        return User::whereIn('id', $this->team_members)->get();
    }

    public function getTeamMemberNames(): string
    {
        return $this->getTeamMemberUsers()->pluck('name')->join(', ');
    }

    public function isTeamMember(User $user): bool
    {
        return in_array($user->id, $this->team_members ?? []);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeWithBudget($query)
    {
        return $query->whereNotNull('pengajuan_anggaran_id');
    }

    public function scopeWithoutBudget($query)
    {
        return $query->whereNull('pengajuan_anggaran_id');
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('project_manager_id', $managerId);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'warning',
            'planning' => 'secondary',
            'in_progress' => 'primary',
            'review' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getPrioritasColorAttribute(): string
    {
        return match($this->prioritas) {
            'low' => 'gray',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'primary',
            default => 'gray',
        };
    }

    public function getFormattedBudgetAttribute(): string
    {
        if ($this->budget) {
            return 'Rp ' . number_format($this->budget, 0, ',', '.');
        }
        if ($this->pengajuanAnggaran) {
            return 'Rp ' . number_format($this->pengajuanAnggaran->total_anggaran, 0, ',', '.');
        }
        return 'Tanpa Budget';
    }

    public function getProgressStatusAttribute(): string
    {
        if ($this->progress_percentage == 0) {
            return 'Belum Dimulai';
        } elseif ($this->progress_percentage < 100) {
            return 'Dalam Progress';
        } else {
            return 'Selesai';
        }
    }

    public function getDaysRemainingAttribute(): int
    {
        $deadline = $this->tanggal_deadline ?: $this->tanggal_selesai;
        if (!$deadline) {
            return 0;
        }
        
        $daysRemaining = now()->diffInDays($deadline, false);
        return $daysRemaining > 0 ? $daysRemaining : 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        $deadline = $this->tanggal_deadline ?: $this->tanggal_selesai;
        if (!$deadline) {
            return false;
        }
        
        return now()->isAfter($deadline) && $this->status !== 'completed';
    }

    // Helper Methods
    public function hasApprovedBudget(): bool
    {
        return $this->pengajuanAnggaran && $this->pengajuanAnggaran->isFullyApproved();
    }

    public function getBudgetAmount(): float
    {
        if ($this->budget) {
            return $this->budget;
        }
        return $this->pengajuanAnggaran ? $this->pengajuanAnggaran->total_anggaran : 0;
    }

    public function getRemainingBudget(): float
    {
        if (!$this->pengajuanAnggaran) {
            return $this->budget ?? 0;
        }
        
        return $this->pengajuanAnggaran->sisa_anggaran;
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'planning';
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, ['in_progress', 'review']);
    }

    public function getTotalTasks(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasks(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    public function getTaskCompletionPercentage(): float
    {
        $total = $this->getTotalTasks();
        if ($total === 0) {
            return 0;
        }
        
        $completed = $this->getCompletedTasks();
        return round(($completed / $total) * 100, 2);
    }

    public function updateProgressFromTasks(): void
    {
        $taskProgress = $this->getTaskCompletionPercentage();
        $this->update(['progress_percentage' => $taskProgress]);
    }

    public function updateProgress(): void
    {
        $this->updateProgressFromTasks();
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    public function markAsActive(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Budget-related methods
    public function spendBudget(float $amount): bool
    {
        if (!$this->pengajuanAnggaran) {
            return false;
        }

        if ($amount > $this->pengajuanAnggaran->sisa_anggaran) {
            return false;
        }

        $this->pengajuanAnggaran->increment('realisasi_anggaran', $amount);
        $this->pengajuanAnggaran->decrement('sisa_anggaran', $amount);
        
        return true;
    }

    public function getBudgetUtilizationPercentage(): float
    {
        if (!$this->pengajuanAnggaran || $this->pengajuanAnggaran->total_anggaran == 0) {
            return 0;
        }

        return round(
            ($this->pengajuanAnggaran->realisasi_anggaran / $this->pengajuanAnggaran->total_anggaran) * 100, 
            2
        );
    }

    public function pengajuanAnggarans(): HasMany
{
    return $this->hasMany(PengajuanAnggaran::class, 'project_id');
}

// Get total anggaran yang disetujui untuk project ini
public function getTotalApprovedBudgetAttribute(): float
{
    return $this->pengajuanAnggarans()
        ->where('status', 'approved')
        ->sum('total_anggaran');
}

// Get total yang sudah terpakai dari semua pengajuan anggaran
public function getTotalUsedBudgetAttribute(): float
{
    return $this->transaksis()
        ->where('status', 'approved')
        ->sum('total_amount');
}

// Get sisa budget yang tersedia
public function getRemainingBudgetAttribute(): float
{
    return $this->total_approved_budget - $this->total_used_budget;
}

// Get persentase penggunaan budget
public function getBudgetUsagePercentageAttribute(): float
{
    if ($this->total_approved_budget == 0) return 0;
    
    return round(($this->total_used_budget / $this->total_approved_budget) * 100, 2);
}

// Check apakah project punya budget
public function hasBudget(): bool
{
    return $this->total_approved_budget > 0;
}

// Get semua pengajuan anggaran yang disetujui
public function getApprovedBudgetRequests()
{
    return $this->pengajuanAnggarans()
        ->where('status', 'approved')
        ->with(['createdBy', 'redaksiApprovedBy', 'keuanganApprovedBy'])
        ->get();
}
}