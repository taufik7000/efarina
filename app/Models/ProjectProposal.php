<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul_proposal',
        'deskripsi',
        'tujuan_project',
        'kategori',
        'prioritas',
        'estimasi_durasi_hari',
        'estimasi_budget',
        'status',
        'catatan_review',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'project_id',
    ];

    protected $casts = [
        'estimasi_budget' => 'decimal:2',
        'estimasi_durasi_hari' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'prioritas' => 'medium',
        'kategori' => 'content',
    ];

    // Relations
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
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

    public function getKategoriColorAttribute(): string
    {
        return match($this->kategori) {
            'content' => 'info',
            'event' => 'warning',
            'campaign' => 'success',
            'research' => 'primary',
            'other' => 'gray',
            default => 'gray',
        };
    }

    public function getFormattedBudgetAttribute(): string
    {
        if ($this->estimasi_budget) {
            return 'Rp ' . number_format($this->estimasi_budget, 0, ',', '.');
        }
        return 'Tidak ada estimasi';
    }

    public function getFormattedDurasiAttribute(): string
    {
        if ($this->estimasi_durasi_hari) {
            return $this->estimasi_durasi_hari . ' hari';
        }
        return 'Tidak ada estimasi';
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasProject(): bool
    {
        return !is_null($this->project_id);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    public function approve(int $reviewerId, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'catatan_review' => $notes,
        ]);

        // Auto-create project saat approve
        $this->createProjectFromApproval();
    }

    public function reject(int $reviewerId, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'catatan_review' => $notes,
        ]);
    }

    public function createProject(array $additionalData = []): Project
    {
        $project = Project::create(array_merge([
            'nama_project' => $this->judul_proposal,
            'deskripsi' => $this->deskripsi,
            'prioritas' => $this->prioritas,
            'created_by' => $this->created_by,
            'status' => 'planning',
        ], $additionalData));

        // Link proposal ke project
        $this->update(['project_id' => $project->id]);

        return $project;
    }

    private function createProjectFromApproval(): Project
    {
        // Estimasi tanggal berdasarkan durasi proposal
        $tanggalMulai = now()->addDays(1); // Mulai besok
        $tanggalSelesai = $this->estimasi_durasi_hari 
            ? $tanggalMulai->copy()->addDays($this->estimasi_durasi_hari)
            : $tanggalMulai->copy()->addDays(30); // Default 30 hari

        return $this->createProject([
            'project_manager_id' => $this->reviewed_by, // Default PM = yang approve
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status' => 'planning', // Pastikan status valid
        ]);
    }
}