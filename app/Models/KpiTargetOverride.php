<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KpiTargetOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpi_target_id',
        'field_name',
        'override_value',
        'original_value',
        'reason',
        'override_type',
        'effective_from',
        'effective_until',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'is_active',
        'additional_data',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'approved_at' => 'datetime',
        'override_value' => 'decimal:2',
        'original_value' => 'decimal:2',
        'is_active' => 'boolean',
        'additional_data' => 'array',
    ];

    // ===== RELATIONS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kpiTarget(): BelongsTo
    {
        return $this->belongsTo(KpiTarget::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===== ACCESSORS =====

    public function getFieldDisplayNameAttribute(): string
    {
        $fieldNames = [
            'min_attendance_rate' => 'Minimum Attendance Rate',
            'max_late_days' => 'Maximum Late Days',
            'max_absent_days' => 'Maximum Absent Days',
            'min_tasks_per_month' => 'Minimum Tasks per Month',
            'min_completion_rate' => 'Minimum Completion Rate',
            'max_overdue_tasks' => 'Maximum Overdue Tasks',
            'target_avg_completion_days' => 'Target Average Completion Days',
            'min_quality_score' => 'Minimum Quality Score',
            'target_client_satisfaction' => 'Target Client Satisfaction',
            'max_revision_rate' => 'Maximum Revision Rate',
        ];

        return $fieldNames[$this->field_name] ?? ucfirst(str_replace('_', ' ', $this->field_name));
    }

    public function getOverrideDisplayAttribute(): string
    {
        $original = $this->original_value ?? 'N/A';
        $new = $this->override_value;
        
        // Format based on field type
        if (str_contains($this->field_name, 'rate') || str_contains($this->field_name, 'satisfaction')) {
            $original = is_numeric($original) ? number_format($original, 2) . '%' : $original;
            $new = number_format($new, 2) . '%';
        } elseif (str_contains($this->field_name, 'days')) {
            $original = is_numeric($original) ? $original . ' days' : $original;
            $new = $new . ' days';
        } else {
            $original = is_numeric($original) ? number_format($original, 2) : $original;
            $new = number_format($new, 2);
        }
        
        return "{$original} → {$new}";
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    public function getOverrideTypeColorAttribute(): string
    {
        return match($this->override_type) {
            'increase' => 'info',
            'decrease' => 'warning',
            'custom' => 'gray',
            default => 'gray'
        };
    }

    public function getPeriodDisplayAttribute(): string
    {
        $from = $this->effective_from->format('d M Y');
        $until = $this->effective_until ? $this->effective_until->format('d M Y') : 'Ongoing';
        
        return "{$from} - {$until}";
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

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTarget($query, int $targetId)
    {
        return $query->where('kpi_target_id', $targetId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForField($query, string $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('effective_until', '<=', now()->addDays($days))
                    ->where('effective_until', '>', now())
                    ->where('status', 'approved');
    }

    // ===== STATIC METHODS =====

    /**
     * Create override request
     */
    public static function createOverrideRequest(
        User $user,
        KpiTarget $target,
        string $fieldName,
        float $newValue,
        string $reason,
        User $requester,
        ?Carbon $effectiveFrom = null,
        ?Carbon $effectiveUntil = null
    ): self {
        // Get original value from target
        $originalValue = $target->getAttribute($fieldName);
        
        // Determine override type
        $overrideType = 'custom';
        if (is_numeric($originalValue) && is_numeric($newValue)) {
            $overrideType = $newValue > $originalValue ? 'increase' : 'decrease';
        }
        
        return self::create([
            'user_id' => $user->id,
            'kpi_target_id' => $target->id,
            'field_name' => $fieldName,
            'override_value' => $newValue,
            'original_value' => $originalValue,
            'reason' => $reason,
            'override_type' => $overrideType,
            'effective_from' => $effectiveFrom ?? now(),
            'effective_until' => $effectiveUntil,
            'status' => 'pending',
            'requested_by' => $requester->id,
            'is_active' => true,
        ]);
    }

    /**
     * Get active overrides for user and target
     */
    public static function getActiveOverridesForUser(User $user, KpiTarget $target, ?Carbon $date = null): array
    {
        $overrides = self::where('user_id', $user->id)
                        ->where('kpi_target_id', $target->id)
                        ->approved()
                        ->active()
                        ->effective($date)
                        ->get();
        
        $result = [];
        foreach ($overrides as $override) {
            $result[$override->field_name] = $override->override_value;
        }
        
        return $result;
    }

    /**
     * Bulk create overrides for multiple users
     */
    public static function bulkCreateOverrides(
        array $userIds,
        KpiTarget $target,
        array $overrides, // ['field_name' => 'new_value']
        string $reason,
        User $requester
    ): array {
        $created = [];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;
            
            foreach ($overrides as $fieldName => $newValue) {
                $created[] = self::createOverrideRequest(
                    $user,
                    $target,
                    $fieldName,
                    $newValue,
                    $reason,
                    $requester
                );
            }
        }
        
        return $created;
    }

    // ===== WORKFLOW METHODS =====

    public function approve(User $approver, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $approver, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $reason,
            'is_active' => false,
        ]);
    }

    public function expire(): bool
    {
        return $this->update([
            'status' => 'expired',
            'is_active' => false,
        ]);
    }

    // ===== VALIDATION METHODS =====

    public function canBeApprovedBy(User $user): bool
    {
        return $user->hasRole(['admin', 'hrd']) && 
               $this->status === 'pending';
    }

    public function canBeEditedBy(User $user): bool
    {
        return ($user->id === $this->requested_by || $user->hasRole(['admin', 'hrd'])) &&
               $this->status === 'pending';
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user) && 
               $this->status === 'pending';
    }

    public function isEffectiveOn(Carbon $date): bool
    {
        if ($this->status !== 'approved' || !$this->is_active) {
            return false;
        }
        
        if ($this->effective_from->gt($date)) {
            return false;
        }
        
        if ($this->effective_until && $this->effective_until->lt($date)) {
            return false;
        }
        
        return true;
    }

    public function validateOverrideValue(): bool
    {
        // Basic validation - can be enhanced based on field type
        $validations = [
            'min_attendance_rate' => ['min' => 0, 'max' => 100],
            'max_late_days' => ['min' => 0, 'max' => 31],
            'max_absent_days' => ['min' => 0, 'max' => 31],
            'min_tasks_per_month' => ['min' => 0, 'max' => 100],
            'min_completion_rate' => ['min' => 0, 'max' => 100],
            'max_overdue_tasks' => ['min' => 0, 'max' => 50],
            'target_avg_completion_days' => ['min' => 0.1, 'max' => 365],
            'min_quality_score' => ['min' => 0, 'max' => 100],
            'target_client_satisfaction' => ['min' => 1, 'max' => 5],
            'max_revision_rate' => ['min' => 0, 'max' => 100],
        ];
        
        if (!isset($validations[$this->field_name])) {
            return true; // No validation rule, assume valid
        }
        
        $rules = $validations[$this->field_name];
        $value = $this->override_value;
        
        return $value >= $rules['min'] && $value <= $rules['max'];
    }

    // ===== HELPER METHODS =====

    public function getDurationInDays(): ?int
    {
        if (!$this->effective_until) {
            return null; // Permanent
        }
        
        return $this->effective_from->diffInDays($this->effective_until);
    }

    public function getRemainingDays(): ?int
    {
        if (!$this->effective_until) {
            return null; // Permanent
        }
        
        if ($this->effective_until->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->effective_until);
    }

    public function getImpactDescription(): string
    {
        $change = $this->override_value - ($this->original_value ?? 0);
        $direction = $change > 0 ? 'increased' : 'decreased';
        $amount = abs($change);
        
        if (str_contains($this->field_name, 'rate') || str_contains($this->field_name, 'satisfaction')) {
            return "Target {$direction} by {$amount}%";
        } elseif (str_contains($this->field_name, 'days')) {
            return "Target {$direction} by {$amount} days";
        } else {
            return "Target {$direction} by {$amount}";
        }
    }

    // ===== BOOT METHODS =====

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($override) {
            // Auto-set requested_by if not already set
            if (!$override->requested_by) {
                $override->requested_by = auth()->id();
            }
            
            // Validate override value
            if (!$override->validateOverrideValue()) {
                throw new \InvalidArgumentException("Invalid override value for field {$override->field_name}");
            }
            
            // Check for overlapping overrides
            $overlapping = self::where('user_id', $override->user_id)
                              ->where('kpi_target_id', $override->kpi_target_id)
                              ->where('field_name', $override->field_name)
                              ->where('status', 'approved')
                              ->where('is_active', true)
                              ->where(function($q) use ($override) {
                                  $q->whereNull('effective_until')
                                    ->orWhere(function($q2) use ($override) {
                                        $q2->where('effective_from', '<=', $override->effective_until ?? now()->addYears(10))
                                           ->where('effective_until', '>=', $override->effective_from);
                                    });
                              })
                              ->exists();
            
            if ($overlapping) {
                throw new \InvalidArgumentException("Overlapping override already exists for this field and period");
            }
        });
        
        static::updated(function ($override) {
            // Auto-expire if past effective_until date
            if ($override->effective_until && 
                $override->effective_until->isPast() && 
                $override->status === 'approved' && 
                $override->is_active) {
                $override->expire();
            }
        });
    }
}