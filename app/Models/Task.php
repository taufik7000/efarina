<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'nama_task',
        'deskripsi',
        'status',
        'prioritas',
        'tanggal_mulai',
        'tanggal_deadline',
        'tanggal_selesai',
        'assigned_to',
        'created_by',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'parent_task_id',
        'order_index',
        'tags',
        'attachments',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'tanggal_selesai' => 'date',
        'tags' => 'array',
        'attachments' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'desc');
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(TaskProgress::class)->orderBy('created_at', 'desc');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'todo' => 'gray',
            'in_progress' => 'info',
            'review' => 'warning',
            'done' => 'success',
            'blocked' => 'danger',
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

    public function updateStatus(string $newStatus, ?string $note = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $newStatus,
            'tanggal_selesai' => $newStatus === 'done' ? now() : null,
        ]);

        // Create progress update record
        if ($note) {
            TaskProgress::create([
                'task_id' => $this->id,
                'user_id' => auth()->id(),
                'progress_note' => $note,
                'progress_percentage' => $this->progress_percentage,
                'status_change' => $newStatus,
            ]);
        }

        // Update project progress
        $this->project->updateProgress();
    }

    public function addComment(string $comment, ?array $attachments = null): TaskComment
    {
        return $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $comment,
            'attachments' => $attachments,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->tanggal_deadline && 
               $this->tanggal_deadline->isPast() && 
               $this->status !== 'done';
    }
}