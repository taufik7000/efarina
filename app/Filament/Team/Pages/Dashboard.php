<?php

namespace App\Filament\Team\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.team.pages.dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;
    
    protected static ?string $title = 'Team Dashboard';

    public function getViewData(): array
    {
        $user = auth()->user();
        
        // My Tasks Statistics
        $myTasks = Task::where('assigned_to', $user->id);
        $myTasksStats = [
            'total' => $myTasks->count(),
            'todo' => $myTasks->clone()->where('status', 'todo')->count(),
            'in_progress' => $myTasks->clone()->where('status', 'in_progress')->count(),
            'review' => $myTasks->clone()->where('status', 'review')->count(),
            'done' => $myTasks->clone()->where('status', 'done')->count(),
            'overdue' => $myTasks->clone()->where('tanggal_deadline', '<', now())->where('status', '!=', 'done')->count(),
        ];

        // Todo Items Statistics
        $tasksWithTodos = Task::where('assigned_to', $user->id)
            ->where('status', '!=', 'done')
            ->whereNotNull('todo_items')
            ->get();
        
        $todoStats = [
            'total_items' => 0,
            'completed_items' => 0,
            'pending_items' => 0,
            'tasks_with_todos' => 0,
        ];

        foreach ($tasksWithTodos as $task) {
            $todoItems = $task->todo_items ?? [];
            if (count($todoItems) > 0) {
                $todoStats['tasks_with_todos']++;
                $todoStats['total_items'] += count($todoItems);
                $todoStats['completed_items'] += count(array_filter($todoItems, fn($item) => $item['completed']));
            }
        }
        
        $todoStats['pending_items'] = $todoStats['total_items'] - $todoStats['completed_items'];

        // My Projects Statistics
        $myProjects = Project::where(function ($query) use ($user) {
            $query->where('project_manager_id', $user->id)
                  ->orWhereJsonContains('team_members', $user->id)
                  ->orWhere('created_by', $user->id);
        });
        
        $myProjectsStats = [
            'total' => $myProjects->count(),
            'active' => $myProjects->clone()->where('status', 'active')->count(),
            'completed' => $myProjects->clone()->where('status', 'completed')->count(),
            'on_hold' => $myProjects->clone()->where('status', 'on_hold')->count(),
        ];

        // Recent Tasks
        $recentTasks = Task::where('assigned_to', $user->id)
                          ->orderBy('updated_at', 'desc')
                          ->with(['project', 'assignedTo'])
                          ->limit(5)
                          ->get();

        // Upcoming Deadlines
        $upcomingDeadlines = Task::where('assigned_to', $user->id)
                                ->where('status', '!=', 'done')
                                ->whereNotNull('tanggal_deadline')
                                ->where('tanggal_deadline', '>=', now())
                                ->where('tanggal_deadline', '<=', now()->addDays(7))
                                ->orderBy('tanggal_deadline')
                                ->with(['project'])
                                ->get();

        // Projects I'm Managing
        $managedProjects = Project::where('project_manager_id', $user->id)
                                 ->orderBy('updated_at', 'desc')
                                 ->limit(3)
                                 ->get();

        // Recent Activity (Comments & Progress Updates)
        $recentActivity = collect();
        
        // Get recent comments from my tasks
        $recentComments = \App\Models\TaskComment::whereHas('task', function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->with(['task', 'user'])->orderBy('created_at', 'desc')->limit(5)->get();
        
        foreach ($recentComments as $comment) {
            $recentActivity->push([
                'type' => 'comment',
                'data' => $comment,
                'created_at' => $comment->created_at,
            ]);
        }

        // Get recent progress updates from my tasks
        $recentProgress = \App\Models\TaskProgress::whereHas('task', function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->with(['task', 'user'])->orderBy('created_at', 'desc')->limit(5)->get();
        
        foreach ($recentProgress as $progress) {
            $recentActivity->push([
                'type' => 'progress',
                'data' => $progress,
                'created_at' => $progress->created_at,
            ]);
        }

        $recentActivity = $recentActivity->sortByDesc('created_at')->take(8);

        return [
            'myTasksStats' => $myTasksStats,
            'todoStats' => $todoStats,
            'myProjectsStats' => $myProjectsStats,
            'recentTasks' => $recentTasks,
            'upcomingDeadlines' => $upcomingDeadlines,
            'managedProjects' => $managedProjects,
            'recentActivity' => $recentActivity,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Team\Widgets\TodoItemsWidget::class,
        ];
    }
}