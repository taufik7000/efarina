<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class KpiTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_type',
        'target_id',
        'target_name',
        'period_type',
        'effective_from',
        'effective_until',
        'min_attendance_rate',
        'max_late_days',
        'max_absent_days',
        'min_tasks_per_month',
        'min_completion_rate',
        'max_overdue_tasks',
        'target_avg_completion_days',
        'min_quality_score',
        'target_client_satisfaction',
        'max_revision_rate',
        'attendance_weight',
        'task_completion_weight',
        'quality_weight',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'min_attendance_rate' => 'decimal:2',
        'min_completion_rate' => 'decimal:2',
        'target_avg_completion_days' => 'decimal:2',
        'min_quality_score' => 'decimal:2',
        'target_client_satisfaction' => 'decimal:2',
        'max_revision_rate' => 'decimal:2',
        'attendance_weight' => 'decimal:2',
        'task_completion_weight' => 'decimal:2',
        'quality_weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONS =====

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function employeeKpis(): HasMany
    {
        return $this->hasMany(EmployeeKpi::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(KpiTargetOverride::class);
    }

    // Dynamic relations based on target_type
    public function targetEntity()
    {
        return match($this->target_type) {
            'jabatan' => $this->belongsTo(Jabatan::class, 'target_id'),
            'individual' => $this->belongsTo(User::class, 'target_id'),
            default => null
        };
    }

    // ===== ACCESSORS =====

    public function getTargetDisplayNameAttribute(): string
    {
        if ($this->target_name) {
            return $this->target_name;
        }

        return match($this->target_type) {
            'global' => 'Global Default',
            'jabatan' => $this->targetEntity?->nama_jabatan ?? 'Unknown Position',
            'individual' => $this->targetEntity?->name ?? 'Unknown Employee',
            default => ucfirst($this->target_type)
        };
    }

    public function getPeriodDisplayAttribute(): string
    {
        $from = $this->effective_from->format('d M Y');
        $until = $this->effective_until ? $this->effective_until->format('d M Y') : 'Ongoing';
        
        return "{$from} - {$until}";
    }

    public function getWeightsSumAttribute(): float
    {
        return $this->attendance_weight + $this->task_completion_weight + $this->quality_weight;
    }

    public function getIsValidWeightsAttribute(): bool
    {
        return abs($this->weights_sum - 100.00) < 0.01; // Allow for floating point precision
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query, ?Carbon $date = null)
    {
        $date = $date ?? now();
        
        return $query->where('effective_from', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', $date);
                    });
    }

    public function scopeForTargetType($query, string $type)
    {
        return $query->where('target_type', $type);
    }

    public function scopeForTarget($query, string $type, ?int $targetId = null)
    {
        $query->where('target_type', $type);
        
        if ($targetId) {
            $query->where('target_id', $targetId);
        }
        
        return $query;
    }

    public function scopeGlobal($query)
    {
        return $query->where('target_type', 'global');
    }

    public function scopeForJabatan($query, int $jabatanId)
    {
        return $query->where('target_type', 'jabatan')
                    ->where('target_id', $jabatanId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('target_type', 'individual')
                    ->where('target_id', $userId);
    }

    // ===== STATIC METHODS =====

    /**
     * Get applicable target for a user at specific date
     */
    public static function getApplicableTarget(User $user, ?Carbon $date = null): ?self
    {
        $date = $date ?? now();
        
        // Priority order: individual > jabatan > global
        
        // 1. Check for individual target
        $target = self::active()
                     ->effective($date)
                     ->forUser($user->id)
                     ->first();
        
        if ($target) {
            return $target;
        }
        
        // 2. Check for jabatan target
        if ($user->jabatan_id) {
            $target = self::active()
                         ->effective($date)
                         ->forJabatan($user->jabatan_id)
                         ->first();
            
            if ($target) {
                return $target;
            }
        }
        
        // 3. Fall back to global target
        return self::active()
                  ->effective($date)
                  ->global()
                  ->first();
    }

    /**
     * Get effective target values for user (including overrides)
     */
    public static function getEffectiveTargetForUser(User $user, ?Carbon $date = null): array
    {
        $baseTarget = self::getApplicableTarget($user, $date);
        
        if (!$baseTarget) {
            return self::getDefaultTargetValues();
        }
        
        $targetValues = $baseTarget->toArray();
        
        // Apply any active overrides
        $overrides = $baseTarget->overrides()
                               ->where('user_id', $user->id)
                               ->where('status', 'approved')
                               ->where('is_active', true)
                               ->effective($date)
                               ->get();
        
        foreach ($overrides as $override) {
            $targetValues[$override->field_name] = $override->override_value;
        }
        
        return $targetValues;
    }

    /**
     * Default target values if no target is found
     */
    public static function getDefaultTargetValues(): array
    {
        return [
            'min_attendance_rate' => 95.00,
            'max_late_days' => 2,
            'max_absent_days' => 1,
            'min_tasks_per_month' => 10,
            'min_completion_rate' => 90.00,
            'max_overdue_tasks' => 1,
            'target_avg_completion_days' => 3.00,
            'min_quality_score' => 80.00,
            'target_client_satisfaction' => 4.00,
            'max_revision_rate' => 20.00,
            'attendance_weight' => 30.00,
            'task_completion_weight' => 40.00,
            'quality_weight' => 30.00,
        ];
    }

    /**
     * Create default global target
     */
    public static function createDefaultGlobalTarget(User $creator): self
    {
        return self::create(array_merge(self::getDefaultTargetValues(), [
            'target_type' => 'global',
            'target_name' => 'Default Global Target',
            'period_type' => 'monthly',
            'effective_from' => now(),
            'is_active' => true,
            'description' => 'Default KPI targets for all employees',
            'created_by' => $creator->id,
        ]));
    }

    // ===== VALIDATION METHODS =====

    public function validateWeights(): bool
    {
        return $this->is_valid_weights;
    }

    public function validateDateRange(): bool
    {
        if (!$this->effective_until) {
            return true; // Open-ended is valid
        }
        
        return $this->effective_from->lte($this->effective_until);
    }

    // ===== HELPER METHODS =====

    public function isEffectiveOn(Carbon $date): bool
    {
        if ($this->effective_from->gt($date)) {
            return false;
        }
        
        if ($this->effective_until && $this->effective_until->lt($date)) {
            return false;
        }
        
        return true;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Allow creator, admin, or HRD to edit
        return $user->id === $this->created_by || 
               $user->hasRole(['admin', 'hrd']);
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Can only delete if no KPIs are using this target
        if ($this->employeeKpis()->exists()) {
            return false;
        }
        
        return $this->canBeEditedBy($user);
    }

    // ===== BOOT METHODS =====

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($target) {
            // Auto-set created_by if not already set
            if (!$target->created_by) {
                $target->created_by = auth()->id();
            }
            
            // Validate weights sum to 100
            if (!$target->validateWeights()) {
                throw new \InvalidArgumentException('Weights must sum to 100%');
            }
            
            // Validate date range
            if (!$target->validateDateRange()) {
                throw new \InvalidArgumentException('Effective from date must be before effective until date');
            }
        });
        
        static::updating(function ($target) {
            // Auto-set updated_by
            if (!$target->updated_by) {
                $target->updated_by = auth()->id();
            }
            
            if (!$target->validateWeights()) {
                throw new \InvalidArgumentException('Weights must sum to 100%');
            }
            
            if (!$target->validateDateRange()) {
                throw new \InvalidArgumentException('Effective from date must be before effective until date');
            }
        });
    }
}