<?php
// Update app/Policies/TaskPolicy.php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'redaksi', 'team']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Redaksi bisa view semua task
        if ($user->hasRole(['redaksi', 'admin'])) {
            return true;
        }

        // Team bisa view task yang terkait dengan mereka
        if ($user->hasRole('team')) {
            return $this->isUserRelatedToTask($user, $task);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
public function create(User $user): bool
{
    // Redaksi bisa create task
    if ($user->hasRole(['redaksi', 'admin'])) {
        return true;
    }
    
    // Team: hanya jika dia adalah PM dari minimal 1 project yang disetujui
    if ($user->hasRole('team')) {
        return \App\Models\Project::where('project_manager_id', $user->id)
            ->whereIn('status', ['in_progress', 'review'])
            ->exists();
    }
    
    return false;
}

    /**
     * Determine whether the user can create task for specific project.
     */
    public function createForProject(User $user, Project $project): bool
    {
        // 1. User harus bisa create task secara umum
        if (!$this->create($user)) {
            return false;
        }

        // 2. Project harus dalam status yang diizinkan
        if (!$this->isProjectAllowTaskCreation($project)) {
            return false;
        }

        // 3. User harus terkait dengan project
        if ($user->hasRole(['redaksi', 'admin'])) {
            return true; // Redaksi bisa create task di semua project
        }

        if ($user->hasRole('team')) {
            return $this->isUserRelatedToProject($user, $project);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Tidak bisa update task jika project sudah completed
        if ($task->project && $task->project->status === 'completed') {
            return false;
        }

        // Redaksi bisa update semua task
        if ($user->hasRole(['redaksi', 'admin'])) {
            return true;
        }

        // Team bisa update task yang terkait dengan mereka
        if ($user->hasRole('team')) {
            return $this->isUserRelatedToTask($user, $task);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Tidak bisa delete task jika project sudah completed
        if ($task->project && $task->project->status === 'completed') {
            return false;
        }

        // Redaksi bisa delete semua task
        if ($user->hasRole(['redaksi', 'admin'])) {
            return true;
        }

        // Team bisa delete task yang mereka buat atau PM dari projectnya
        if ($user->hasRole('team')) {
            return $user->id === $task->created_by
                || ($task->project && $user->id === $task->project->project_manager_id);
        }

        return false;
    }

    /**
     * Check if project allows task creation
     */
    private function isProjectAllowTaskCreation(Project $project): bool
    {
        // Task hanya bisa dibuat jika project sudah disetujui dan belum selesai
        return in_array($project->status, ['in_progress', 'review']);
    }

    /**
     * Check if user is related to project
     */
    private function isUserRelatedToProject(User $user, Project $project): bool
    {
        return $user->id === $project->project_manager_id
            || $user->id === $project->created_by
            || (is_array($project->team_members) && in_array($user->id, $project->team_members));
    }

    /**
     * Check if user is related to task
     */
    private function isUserRelatedToTask(User $user, Task $task): bool
    {
        // User terkait dengan task jika:
        // 1. Assigned ke task tersebut
        // 2. Creator task tersebut  
        // 3. PM dari project tersebut
        // 4. Member dari project tersebut
        
        if ($user->id === $task->assigned_to || $user->id === $task->created_by) {
            return true;
        }

        if ($task->project) {
            return $this->isUserRelatedToProject($user, $task->project);
        }

        return false;
    }

    public function addComment(User $user, Task $task): bool
    {
        // Izinkan jika pengguna adalah Project Manager dari proyek terkait.
        if ($task->project && $user->id === $task->project->project_manager_id) {
            return true;
        }

        // Izinkan jika pengguna adalah bagian dari tim proyek.
        // Pastikan kolom 'team_members' berisi array ID.
        if ($task->project && is_array($task->project->team_members)) {
            return in_array($user->id, $task->project->team_members);
        }

        // Tolak jika tidak memenuhi kondisi di atas.
        return false;
    }
}