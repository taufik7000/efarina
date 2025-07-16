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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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
            'draft' => 'gray',
            'approved' => 'success',
            'active' => 'primary',
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
        if (!$this->tanggal_selesai) {
            return 0;
        }
        
        $daysRemaining = now()->diffInDays($this->tanggal_selesai, false);
        return $daysRemaining > 0 ? $daysRemaining : 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->tanggal_selesai) {
            return false;
        }
        
        return now()->isAfter($this->tanggal_selesai) && $this->status !== 'completed';
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

    public function getRemainingBudget(): float
    {
        if (!$this->pengajuanAnggaran) {
            return 0;
        }
        
        return $this->pengajuanAnggaran->sisa_anggaran;
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'draft' || $this->status === 'approved';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'active' && $this->progress_percentage >= 100;
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

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    public function markAsActive(): void
    {
        $this->update(['status' => 'active']);
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
}