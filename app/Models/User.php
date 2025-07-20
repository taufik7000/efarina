<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

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
        'monthly_leave_quota',     // 🔥 TAMBAHAN BARU
        'employment_start_date',   // 🔥 TAMBAHAN BARU
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

// News yang ditulis user sebagai author
public function authoredNews(): HasMany
{
    return $this->hasMany(News::class, 'author_id');
}

// News yang diedit user sebagai editor
public function editedNews(): HasMany
{
    return $this->hasMany(News::class, 'editor_id');
}

// News categories yang dibuat user
public function createdNewsCategories(): HasMany
{
    return $this->hasMany(NewsCategory::class, 'created_by');
}

// News tags yang dibuat user
public function createdNewsTags(): HasMany
{
    return $this->hasMany(NewsTag::class, 'created_by');
}

// Helper method untuk cek apakah user bisa manage news
public function canManageNews(): bool
{
    return $this->hasRole(['admin', 'redaksi']);
}

// Helper method untuk cek apakah user bisa publish news
public function canPublishNews(): bool
{
    return $this->hasRole(['admin', 'redaksi']);
}

public function leaveRequests(): HasMany
{
    return $this->hasMany(LeaveRequest::class);
}

public function approvedLeaveRequests(): HasMany
{
    return $this->hasMany(LeaveRequest::class, 'approved_by');
}

/**
 * Hitung kuota cuti yang sudah digunakan dalam bulan tertentu
 */
public function getUsedLeaveQuotaInMonth($year, $month): int
{
    return $this->leaveRequests()
        ->approved()
        ->inMonth($year, $month)
        ->sum('total_days');
}

/**
 * Hitung sisa kuota cuti dalam bulan tertentu
 */
public function getRemainingLeaveQuotaInMonth($year, $month): int
{
    $used = $this->getUsedLeaveQuotaInMonth($year, $month);
    return max(0, $this->monthly_leave_quota - $used);
}

/**
 * Cek apakah user masih punya kuota cuti untuk sejumlah hari
 */
public function hasLeaveQuotaFor($days, $year, $month): bool
{
    return $this->getRemainingLeaveQuotaInMonth($year, $month) >= $days;
}

/**
 * Hitung total hari kerja dalam bulan (excluding Sundays)
 */
public function getWorkingDaysInMonth($year, $month): int
{
    $start = Carbon::create($year, $month, 1);
    $end = $start->copy()->endOfMonth();
    $workingDays = 0;

    while ($start->lte($end)) {
        if ($start->dayOfWeek !== Carbon::SUNDAY) {
            $workingDays++;
        }
        $start->addDay();
    }

    return $workingDays;
}


}