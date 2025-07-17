<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Berikan izin super-admin untuk melakukan semua tindakan.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Peran 'admin' dan 'super-admin' bisa melakukan apa saja.
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah pengguna dapat melihat daftar task.
     * Logika: Semua pengguna yang login dapat melihat.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail sebuah task.
     * Logika: Semua pengguna yang login dapat melihat.
     */
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat membuat task baru.
     * Logika: 
     * - Redaksi: bisa membuat task dari project yang sudah ada
     * - Team: hanya jika dia Project Manager dari project tersebut
     */
    public function create(User $user): bool
    {
        // Redaksi bisa membuat task dari project manapun
        if ($user->hasRole('redaksi')) {
            return true;
        }

        // Team bisa membuat task jika ada project yang dia kelola sebagai PM
        if ($user->hasRole('team')) {
            // Cek apakah user ini PM dari setidaknya satu project
            return \App\Models\Project::where('project_manager_id', $user->id)->exists();
        }

        // Role lain (direktur, keuangan, marketing, hrd) tidak bisa membuat task
        return false;
    }

    /**
     * Menentukan apakah pengguna dapat mengedit sebuah task.
     * Logika: Hanya redaksi dan team yang bisa edit
     */
    public function update(User $user, Task $task): bool
    {
        // Redaksi bisa edit semua task
        if ($user->hasRole('redaksi')) {
            return true;
        }

        // Team bisa edit jika:
        // - Dia yang membuat task tersebut, ATAU
        // - Dia assigned ke task tersebut, ATAU  
        // - Dia PM dari project task tersebut
        if ($user->hasRole('team')) {
            return $user->id === $task->created_by
                || $user->id === $task->assigned_to
                || ($task->project && $user->id === $task->project->project_manager_id);
        }

        // Role lain tidak bisa edit
        return false;
    }

    /**
     * Menentukan apakah pengguna dapat menghapus sebuah task.
     * Logika: Hanya redaksi dan team yang bisa hapus
     */
    public function delete(User $user, Task $task): bool
    {
        // Redaksi bisa hapus semua task
        if ($user->hasRole('redaksi')) {
            return true;
        }

        // Team hanya bisa hapus task yang dia buat atau PM dari projectnya
        if ($user->hasRole('team')) {
            return $user->id === $task->created_by
                || ($task->project && $user->id === $task->project->project_manager_id);
        }

        // Role lain tidak bisa hapus
        return false;
    }

    /**
     * Menentukan apakah pengguna dapat melakukan hapus massal.
     * Logika: Sama dengan delete
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole(['redaksi', 'team']);
    }

    /**
     * Menentukan apakah pengguna dapat menambahkan komentar pada sebuah task.
     */
    public function addComment(User $user, Task $task): bool
    {
        // Redaksi bisa komen di semua task
        if ($user->hasRole('redaksi')) {
            return true;
        }

        // Team bisa komen jika terlibat dengan task
        if ($user->hasRole('team')) {
            // Izinkan jika pengguna adalah Project Manager dari proyek terkait
            if ($task->project && $user->id === $task->project->project_manager_id) {
                return true;
            }

            // Izinkan jika assigned ke task ini
            if ($user->id === $task->assigned_to) {
                return true;
            }

            // Izinkan jika pembuat task
            if ($user->id === $task->created_by) {
                return true;
            }

            // Izinkan jika pengguna adalah bagian dari tim proyek
            if ($task->project && is_array($task->project->team_members)) {
                return in_array($user->id, $task->project->team_members);
            }
        }

        // Role lain tidak bisa komen
        return false;
    }
}