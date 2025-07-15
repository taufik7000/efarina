<?php

namespace App\Filament\Team\Widgets;

use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TodoItemsWidget extends Widget
{
    protected static string $view = 'filament.team.widgets.todo-items-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 3;

    public function getTodoTasks(): Collection
    {
        return Task::where('assigned_to', auth()->id())
            ->where('status', '!=', 'done')
            ->whereNotNull('todo_items')
            ->with(['project'])
            ->get()
            ->filter(function ($task) {
                $todoItems = $task->todo_items ?? [];
                return count($todoItems) > 0;
            })
            ->map(function ($task) {
                $todoItems = $task->todo_items ?? [];
                $incompleteItems = array_filter($todoItems, fn($item) => !$item['completed']);
                
                return [
                    'task' => $task,
                    'incomplete_todos' => array_values($incompleteItems),
                    'total_todos' => count($todoItems),
                    'completed_todos' => count($todoItems) - count($incompleteItems),
                ];
            })
            ->sortBy(function ($item) {
                return $item['task']->tanggal_deadline ?? now()->addYears(1);
            });
    }

    public function toggleTodoItem($taskId, $itemId)
    {
        $task = Task::find($taskId);
        
        if (!$task || $task->assigned_to !== auth()->id()) {
            return;
        }

        $todoItems = $task->todo_items ?? [];
        $itemIndex = array_search($itemId, array_column($todoItems, 'id'));
        
        if ($itemIndex !== false) {
            $currentStatus = $todoItems[$itemIndex]['completed'] ?? false;
            $task->updateTodoItem($itemId, !$currentStatus, 'Todo item updated from dashboard');
        }
    }
}