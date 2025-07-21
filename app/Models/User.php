<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Tambahkan ini
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

    // ===== RELASI EMPLOYEE PROFILE (BARU) =====
    
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

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function taskProgress(): HasMany
    {
        return $this->hasMany(TaskProgress::class);
    }

    public function createdBudgetPlans(): HasMany
    {
        return $this->hasMany(BudgetPlan::class, 'created_by');
    }

    public function approvedBudgetPlans(): HasMany
    {
        return $this->hasMany(BudgetPlan::class, 'approved_by');
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
        return $query->whereHas('employeeProfile', function($q) {
            $q->complete();
        });
    }

    /**
     * Users with incomplete profiles
     */
    public function scopeWithIncompleteProfile($query)
    {
        return $query->whereDoesntHave('employeeProfile')
                    ->orWhereHas('employeeProfile', function($q) {
                        $q->incomplete();
                    });
    }

    /**
     * Users yang punya dokumen tertentu
     */
    public function scopeHasDocument($query, string $documentType)
    {
        return $query->whereHas('employeeDocuments', function($q) use ($documentType) {
            $q->where('document_type', $documentType);
        });
    }

    /**
     * Users dengan dokumen yang belum diverifikasi
     */
    public function scopeWithUnverifiedDocuments($query)
    {
        return $query->whereHas('employeeDocuments', function($q) {
            $q->unverified();
        });
    }
}