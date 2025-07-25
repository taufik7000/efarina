<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'monthly_leave_quota',
        'employment_start_date',
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
            'employment_start_date' => 'date',
        ];
    }

    // ===== RELASI EMPLOYEE PROFILE & DOCUMENTS =====
    
    /**
     * Employee profile detail
     */
    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    /**
     * Employee documents
     */
    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    // ===== RELASI YANG SUDAH ADA =====
    
    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    // PROJECT MANAGEMENT RELATIONS

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

    // NEWS RELATIONS

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

    // LEAVE REQUEST RELATIONS

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

    // COMPENSATION RELATIONS

    /**
     * Relasi ke compensations
     */
    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }

    /**
     * Relasi ke compensations yang disetujui user ini
     */
    public function approvedCompensations(): HasMany
    {
        return $this->hasMany(Compensation::class, 'approved_by');
    }

    /**
     * Get available compensations yang bisa digunakan
     */
    public function getAvailableCompensations()
    {
        return $this->compensations()->available()->orderBy('expires_at', 'asc')->get();
    }

    /**
     * Get total available compensation days
     */
    public function getTotalAvailableCompensationDays(): int
    {
        return $this->compensations()->available()->count();
    }

    /**
     * Get compensations yang akan expired dalam 30 hari
     */
    public function getExpiringCompensations($days = 30)
    {
        return $this->compensations()
            ->where('status', 'earned')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'asc')
            ->get();
    }

    /**
     * Check apakah user punya kompensasi tersedia untuk tanggal tertentu
     */
    public function hasAvailableCompensationFor(Carbon $date): bool
    {
        // Tidak bisa pakai kompensasi untuk hari Minggu
        if ($date->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }

        return $this->compensations()
            ->available()
            ->where('expires_at', '>', $date)
            ->exists();
    }

    /**
     * Use compensation for specific date
     */
    public function useCompensationFor(Carbon $date, ?string $notes = null): ?Compensation
    {
        if (!$this->hasAvailableCompensationFor($date)) {
            return null;
        }

        // Ambil kompensasi yang paling lama (FIFO)
        $compensation = $this->compensations()
            ->available()
            ->where('expires_at', '>', $date)
            ->orderBy('expires_at', 'asc')
            ->first();

        if ($compensation && $compensation->use($date, $notes)) {
            return $compensation;
        }

        return null;
    }

    /**
     * Create compensation dari kerja di hari libur
     */
    public function createCompensationFromHolidayWork(
        Carbon $workDate,
        Carbon $startTime,
        Carbon $endTime,
        string $reason,
        ?int $approverId = null
    ): Compensation {
        $workHours = $endTime->diffInHours($startTime);

        // Kompensasi expired dalam 90 hari (bisa disesuaikan policy)
        $expiresAt = $workDate->copy()->addDays(90);

        return $this->compensations()->create([
            'work_date' => $workDate,
            'work_start_time' => $startTime->format('H:i:s'),
            'work_end_time' => $endTime->format('H:i:s'),
            'work_hours' => $workHours,
            'work_reason' => $reason,
            'expires_at' => $expiresAt,
            'approved_by' => $approverId,
            'approved_at' => $approverId ? now() : null,
            'status' => $approverId ? 'earned' : 'earned', // Auto approved untuk sekarang
        ]);
    }

    // ===== HELPER METHODS UNTUK EMPLOYEE PROFILE =====

    /**
     * Get atau create employee profile
     */
    public function getOrCreateProfile(): EmployeeProfile
    {
        return $this->employeeProfile ?: $this->employeeProfile()->create([]);
    }

    /**
     * Check if user has complete profile
     */
    public function hasCompleteProfile(): bool
    {
        return $this->employeeProfile && $this->employeeProfile->isProfileComplete();
    }

    /**
     * Get profile completion percentage
     */
    public function getProfileCompletionPercentage(): int
    {
        if (!$this->employeeProfile) {
            return 0;
        }

        return $this->employeeProfile->getProfileCompletionPercentage();
    }

    /**
     * Get specific document by type
     */
    public function getDocument(string $type): ?EmployeeDocument
    {
        return $this->employeeDocuments()->where('document_type', $type)->first();
    }

    /**
     * Check if user has specific document
     */
    public function hasDocument(string $type): bool
    {
        return $this->employeeDocuments()->where('document_type', $type)->exists();
    }

    /**
     * Get verified documents count
     */
    public function getVerifiedDocumentsCount(): int
    {
        return $this->employeeDocuments()->verified()->count();
    }

    /**
     * Get unverified documents count
     */
    public function getUnverifiedDocumentsCount(): int
    {
        return $this->employeeDocuments()->unverified()->count();
    }

    /**
     * Get full employee info dengan profile
     */
    public function getFullEmployeeInfoAttribute(): array
    {
        $profile = $this->employeeProfile;

        return [
            'basic' => [
                'name' => $this->name,
                'email' => $this->email,
                'jabatan' => $this->jabatan?->nama_jabatan,
                'divisi' => $this->jabatan?->divisi?->nama_divisi,
                'employment_start' => $this->employment_start_date?->format('d M Y'),
            ],
            'personal' => [
                'nik_ktp' => $profile?->nik_ktp,
                'birth_place_date' => $profile?->birth_place_full,
                'age' => $profile?->age,
                'address' => $profile?->alamat,
                'emergency_contact' => $profile ? $profile->kontak_darurat_nama . ' (' . $profile->kontak_darurat_telp . ')' : null,
            ],
            'financial' => [
                'salary' => $profile?->formatted_gaji,
                'account' => $profile?->masked_rekening,
                'npwp' => $profile?->masked_npwp,
            ],
            'completion' => [
                'profile_complete' => $this->hasCompleteProfile(),
                'completion_percentage' => $this->getProfileCompletionPercentage(),
                'documents_verified' => $this->getVerifiedDocumentsCount(),
                'documents_pending' => $this->getUnverifiedDocumentsCount(),
            ]
        ];
    }

    // ===== SCOPES =====

    /**
     * Users with complete profiles
     */
    public function scopeWithCompleteProfile($query)
    {
        return $query->whereHas('employeeProfile', function ($q) {
            $q->complete();
        });
    }

    /**
     * Users with incomplete profiles
     */
    public function scopeWithIncompleteProfile($query)
    {
        return $query->whereDoesntHave('employeeProfile')
            ->orWhereHas('employeeProfile', function ($q) {
                $q->incomplete();
            });
    }

    /**
     * Users yang punya dokumen tertentu
     */
    public function scopeHasDocument($query, string $documentType)
    {
        return $query->whereHas('employeeDocuments', function ($q) use ($documentType) {
            $q->where('document_type', $documentType);
        });
    }

    /**
     * Users dengan dokumen yang belum diverifikasi
     */
    public function scopeWithUnverifiedDocuments($query)
    {
        return $query->whereHas('employeeDocuments', function ($q) {
            $q->unverified();
        });
    }
}