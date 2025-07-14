<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'progress_note',
        'progress_percentage',
        'status_change',
        'hours_worked',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getStatusChangeColorAttribute(): string
    {
        if (!$this->status_change) {
            return 'gray';
        }

        return match($this->status_change) {
            'todo' => 'gray',
            'in_progress' => 'info',
            'review' => 'warning',
            'done' => 'success',
            'blocked' => 'danger',
            default => 'gray',
        };
    }
}