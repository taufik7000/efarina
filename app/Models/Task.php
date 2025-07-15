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
        'todo_items', // 👈 TAMBAHAN BARU
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'tanggal_selesai' => 'date',
        'tags' => 'array',
        'attachments' => 'array',
        'todo_items' => 'array', // 👈 TAMBAHAN BARU
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

    // 👇 METHODS BARU UNTUK TODO ITEMS

    /**
     * Get todo items dengan format yang konsisten
     */
    public function getTodoItemsAttribute($value): array
    {
        if (!$value) {
            return [];
        }

        $items = json_decode($value, true) ?? [];
        
        // Pastikan setiap item memiliki struktur yang benar
        return array_map(function ($item, $index) {
            return [
                'id' => $item['id'] ?? $index,
                'text' => $item['text'] ?? '',
                'completed' => $item['completed'] ?? false,
                'created_at' => $item['created_at'] ?? now()->toISOString(),
                'completed_at' => $item['completed_at'] ?? null,
                'completed_by' => $item['completed_by'] ?? null,
            ];
        }, $items, array_keys($items));
    }

    /**
     * Update single todo item
     */
    public function updateTodoItem(int $itemId, bool $completed, ?string $note = null): void
    {
        $todoItems = $this->todo_items ?? [];
        
        // Cari item berdasarkan ID
        $itemIndex = array_search($itemId, array_column($todoItems, 'id'));
        
        if ($itemIndex !== false) {
            $todoItems[$itemIndex]['completed'] = $completed;
            $todoItems[$itemIndex]['completed_at'] = $completed ? now()->toISOString() : null;
            $todoItems[$itemIndex]['completed_by'] = $completed ? auth()->id() : null;
            
            $this->update(['todo_items' => $todoItems]);
            
            // Auto-update progress percentage
            $this->updateProgressFromTodoItems();
            
            // Log progress update
            if ($note) {
                TaskProgress::create([
                    'task_id' => $this->id,
                    'user_id' => auth()->id(),
                    'progress_note' => $note,
                    'progress_percentage' => $this->progress_percentage,
                    'status_change' => null,
                ]);
            }
        }
    }

    /**
     * Add new todo item
     */
    public function addTodoItem(string $text): void
    {
        $todoItems = $this->todo_items ?? [];
        $newId = empty($todoItems) ? 1 : max(array_column($todoItems, 'id')) + 1;
        
        $todoItems[] = [
            'id' => $newId,
            'text' => $text,
            'completed' => false,
            'created_at' => now()->toISOString(),
            'completed_at' => null,
            'completed_by' => null,
        ];
        
        $this->update(['todo_items' => $todoItems]);
    }

    /**
     * Remove todo item
     */
    public function removeTodoItem(int $itemId): void
    {
        $todoItems = $this->todo_items ?? [];
        $todoItems = array_filter($todoItems, fn($item) => $item['id'] !== $itemId);
        
        $this->update(['todo_items' => array_values($todoItems)]);
        $this->updateProgressFromTodoItems();
    }

    /**
     * Update progress percentage berdasarkan todo items
     */
    public function updateProgressFromTodoItems(): void
    {
        $todoItems = $this->todo_items ?? [];
        
        if (empty($todoItems)) {
            return;
        }
        
        $totalItems = count($todoItems);
        $completedItems = count(array_filter($todoItems, fn($item) => $item['completed']));
        
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        
        $this->update(['progress_percentage' => $progressPercentage]);
        
        // Update project progress
        $this->project->updateProgress();
    }

    /**
     * Get todo completion statistics
     */
    public function getTodoStatsAttribute(): array
    {
        $todoItems = $this->todo_items ?? [];
        $total = count($todoItems);
        $completed = count(array_filter($todoItems, fn($item) => $item['completed']));
        
        return [
            'total' => $total,
            'completed' => $completed,
            'remaining' => $total - $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }
}