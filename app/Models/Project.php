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
        'status',
        'prioritas',
        'tanggal_mulai',
        'tanggal_deadline',
        'tanggal_selesai',
        'project_manager_id',
        'divisi_id',
        'budget',
        'progress_percentage',
        'team_members',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'tanggal_selesai' => 'date',
        'team_members' => 'array',
        'budget' => 'decimal:2',
    ];

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getTeamMembersUsersAttribute()
    {
        if (!$this->team_members) {
            return collect();
        }
        
        return User::whereIn('id', $this->team_members)->get();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'active' => 'info',
            'on_hold' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getPrioritasColorAttribute(): string
    {
        return match($this->prioritas) {
            'low' => 'gray',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'gray',
        };
    }

    public function updateProgress(): void
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            $this->update(['progress_percentage' => 0]);
            return;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        $progressPercentage = round(($completedTasks / $totalTasks) * 100);

        $this->update(['progress_percentage' => $progressPercentage]);

        // Auto update status based on progress
        if ($progressPercentage === 100 && $this->status !== 'completed') {
            $this->update([
                'status' => 'completed',
                'tanggal_selesai' => now(),
            ]);
        }
    }
}