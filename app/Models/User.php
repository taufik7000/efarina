<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // 👈 TAMBAHKAN INI
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELASI YANG SUDAH ADA
    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }
    
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    // 👇 TAMBAHKAN RELASI PROJECT MANAGEMENT INI
    
    /**
     * Projects yang di-manage oleh user ini
     */
    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    /**
     * Tasks yang di-assign ke user ini
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Tasks yang dibuat oleh user ini
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Comments yang dibuat oleh user ini
     */
    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Progress updates yang dibuat oleh user ini
     */
    public function taskProgress(): HasMany
    {
        return $this->hasMany(TaskProgress::class);
    }

    /**
 * Budget plans yang dibuat oleh user ini
 */
public function createdBudgetPlans(): HasMany
{
    return $this->hasMany(BudgetPlan::class, 'created_by');
}

/**
 * Budget plans yang di-approve oleh user ini
 */
public function approvedBudgetPlans(): HasMany
{
    return $this->hasMany(BudgetPlan::class, 'approved_by');
}

/**
 * Budget categories yang dibuat oleh user ini
 */
public function createdBudgetCategories(): HasMany
{
    return $this->hasMany(BudgetCategory::class, 'created_by');
}

/**
 * Transaksi yang dibuat oleh user ini
 */
public function createdTransaksis(): HasMany
{
    return $this->hasMany(Transaksi::class, 'created_by');
}

/**
 * Transaksi yang di-approve oleh user ini
 */
public function approvedTransaksis(): HasMany
{
    return $this->hasMany(Transaksi::class, 'approved_by');
}



}