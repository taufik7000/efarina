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
     * Logika: Hanya pengguna dengan peran tertentu yang bisa membuat.
     */
    public function create(User $user): bool
    {
        // Hanya peran ini yang bisa membuat task baru.
        return $user->hasRole(['admin', 'super-admin', 'direktur', 'team']);
    }

    /**
     * Menentukan apakah pengguna dapat mengedit sebuah task.
     * Logika: Admin/super-admin bisa edit semua.
     * Pengguna lain bisa edit jika dia adalah Project Manager, assignee, atau pembuat task.
     */
    public function update(User $user, Task $task): bool
    {
        // Pengecekan untuk super-admin/admin sudah ditangani di method before().
        // Jadi, logika di sini berlaku untuk peran lain seperti 'team'.
        return $user->id === $task->assigned_to
            || $user->id === $task->created_by
            || ($task->project && $user->id === $task->project->project_manager_id);
    }

    /**
     * Menentukan apakah pengguna dapat menghapus sebuah task.
     * Logika: Hanya peran dengan hak tinggi (admin, super-admin, direktur) yang bisa.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole(['admin', 'super-admin', 'direktur']);
    }

    /**
     * Menentukan apakah pengguna dapat melakukan hapus massal.
     * Logika: Sama dengan delete, hanya peran dengan hak tinggi.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super-admin', 'direktur']);
    }

        /**
     * Menentukan apakah pengguna dapat menambahkan komentar pada sebuah task.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return bool
     */
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