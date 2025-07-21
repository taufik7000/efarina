<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KpiReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_kpi_id',
        'reviewer_id',
        'review_type',
        'review_date',
        'review_status',
        'attendance_assessment',
        'attendance_feedback',
        'task_performance_assessment',
        'task_performance_feedback',
        'quality_assessment',
        'quality_feedback',
        'collaboration_assessment',
        'collaboration_feedback',
        'initiative_assessment',
        'initiative_feedback',
        'overall_manager_rating',
        'strengths',
        'areas_for_improvement',
        'development_suggestions',
        'goals_for_next_period',
        'action_items',
        'support_needed',
        'employee_self_assessment',
        'employee_concerns',
        'employee_suggestions',
        'employee_acknowledged',
        'employee_acknowledged_at',
        'requires_followup',
        'followup_date',
        'followup_notes',
        'review_duration_minutes',
        'attachments',
        'is_final',
    ];

    protected $casts = [
        'review_date' => 'date',
        'employee_acknowledged_at' => 'datetime',
        'followup_date' => 'date',
        'attendance_assessment' => 'decimal:2',
        'task_performance_assessment' => 'decimal:2',
        'quality_assessment' => 'decimal:2',
        'collaboration_assessment' => 'decimal:2',
        'initiative_assessment' => 'decimal:2',
        'overall_manager_rating' => 'decimal:2',
        'goals_for_next_period' => 'array',
        'action_items' => 'array',
        'attachments' => 'array',
        'employee_acknowledged' => 'boolean',
        'requires_followup' => 'boolean',
        'is_final' => 'boolean',
    ];

    // ===== RELATIONS =====

    public function employeeKpi(): BelongsTo
    {
        return $this->belongsTo(EmployeeKpi::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Convenient accessors to employee through KPI
    public function getEmployeeAttribute(): ?User
    {
        return $this->employeeKpi?->user;
    }

    // ===== ACCESSORS =====

    public function getReviewTypeDisplayAttribute(): string
    {
        return match($this->review_type) {
            'monthly' => 'Monthly Review',
            'quarterly' => 'Quarterly Review',
            'annual' => 'Annual Review',
            'special' => 'Special Review',
            default => ucfirst($this->review_type)
        };
    }

    public function getReviewStatusColorAttribute(): string
    {
        return match($this->review_status) {
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray'
        };
    }

    public function getOverallAssessmentAttribute(): array
    {
        $assessments = [
            'attendance' => $this->attendance_assessment,
            'task_performance' => $this->task_performance_assessment,
            'quality' => $this->quality_assessment,
            'collaboration' => $this->collaboration_assessment,
            'initiative' => $this->initiative_assessment,
        ];

        $validAssessments = array_filter($assessments, fn($val) => $val !== null);
        
        if (empty($validAssessments)) {
            return [
                'average' => null,
                'count' => 0,
                'breakdown' => $assessments,
            ];
        }

        return [
            'average' => round(array_sum($validAssessments) / count($validAssessments), 2),
            'count' => count($validAssessments),
            'breakdown' => $assessments,
        ];
    }

    public function getCompletionPercentageAttribute(): float
    {
        $requiredFields = [
            'attendance_assessment',
            'task_performance_assessment', 
            'quality_assessment',
            'overall_manager_rating',
            'strengths',
            'areas_for_improvement',
        ];

        $completed = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($requiredFields)) * 100, 1);
    }

    public function getReviewSummaryAttribute(): string
    {
        $summary = [];
        
        if ($this->overall_manager_rating) {
            $rating = $this->overall_manager_rating;
            $ratingText = match(true) {
                $rating >= 4.5 => 'Excellent',
                $rating >= 4.0 => 'Very Good',
                $rating >= 3.5 => 'Good',
                $rating >= 3.0 => 'Satisfactory',
                $rating >= 2.5 => 'Needs Improvement',
                default => 'Poor'
            };
            $summary[] = "Overall Rating: {$ratingText} ({$rating}/5)";
        }

        if ($this->goals_for_next_period && count($this->goals_for_next_period) > 0) {
            $summary[] = count($this->goals_for_next_period) . " goals set for next period";
        }

        if ($this->requires_followup) {
            $summary[] = "Follow-up required";
        }

        return implode(' • ', $summary) ?: 'Review in progress';
    }

    // ===== SCOPES =====

    public function scopeForEmployee($query, int $userId)
    {
        return $query->whereHas('employeeKpi', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeByReviewer($query, int $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('review_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('review_status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('review_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('review_status', ['scheduled', 'in_progress']);
    }

    public function scopeRequiresFollowup($query)
    {
        return $query->where('requires_followup', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('review_date', '<', now())
                    ->whereIn('review_status', ['scheduled', 'in_progress']);
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('review_date', [now(), now()->addDays($days)])
                    ->where('review_status', 'scheduled');
    }

    public function scopeForPeriod($query, int $year, ?int $month = null)
    {
        return $query->whereHas('employeeKpi', function($q) use ($year, $month) {
            $q->where('period_year', $year);
            if ($month) {
                $q->where('period_month', $month);
            }
        });
    }

    // ===== STATIC METHODS =====

    /**
     * Create review for KPI
     */
    public static function createReview(
        EmployeeKpi $kpi,
        User $reviewer,
        string $type = 'monthly',
        ?Carbon $reviewDate = null
    ): self {
        return self::create([
            'employee_kpi_id' => $kpi->id,
            'reviewer_id' => $reviewer->id,
            'review_type' => $type,
            'review_date' => $reviewDate ?? now(),
            'review_status' => 'scheduled',
        ]);
    }

    /**
     * Bulk create reviews for multiple KPIs
     */
    public static function bulkCreateReviews(
        array $kpiIds,
        User $reviewer,
        string $type = 'monthly',
        ?Carbon $reviewDate = null
    ): array {
        $created = [];
        
        foreach ($kpiIds as $kpiId) {
            $kpi = EmployeeKpi::find($kpiId);
            if ($kpi) {
                $created[] = self::createReview($kpi, $reviewer, $type, $reviewDate);
            }
        }
        
        return $created;
    }

    /**
     * Get review statistics for manager
     */
    public static function getReviewStats(User $reviewer, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $query = self::byReviewer($reviewer->id)->forPeriod($year, $month);
        
        $total = $query->count();
        $completed = $query->clone()->completed()->count();
        $pending = $query->clone()->pending()->count();
        $overdue = $query->clone()->overdue()->count();
        $requiresFollowup = $query->clone()->requiresFollowup()->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'overdue' => $overdue,
            'requires_followup' => $requiresFollowup,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    // ===== WORKFLOW METHODS =====

    public function startReview(): bool
    {
        if ($this->review_status !== 'scheduled') {
            return false;
        }

        return $this->update([
            'review_status' => 'in_progress',
        ]);
    }

    public function completeReview(array $data = []): bool
    {
        if (!in_array($this->review_status, ['scheduled', 'in_progress'])) {
            return false;
        }

        $updateData = array_merge($data, [
            'review_status' => 'completed',
            'is_final' => true,
        ]);

        return $this->update($updateData);
    }

    public function cancelReview(string $reason = ''): bool
    {
        return $this->update([
            'review_status' => 'cancelled',
            'followup_notes' => $reason,
        ]);
    }

    public function acknowledgeByEmployee(User $employee, ?string $notes = null): bool
    {
        if ($employee->id !== $this->employeeKpi->user_id) {
            return false;
        }

        return $this->update([
            'employee_acknowledged' => true,
            'employee_acknowledged_at' => now(),
            'employee_suggestions' => $notes,
        ]);
    }

    public function scheduleFollowup(Carbon $date, string $notes = ''): bool
    {
        return $this->update([
            'requires_followup' => true,
            'followup_date' => $date,
            'followup_notes' => $notes,
        ]);
    }

    // ===== VALIDATION METHODS =====

    public function canBeEditedBy(User $user): bool
    {
        // Reviewer can edit until finalized
        if ($user->id === $this->reviewer_id && !$this->is_final) {
            return true;
        }

        // Admin/HRD can always edit
        return $user->hasRole(['admin', 'hrd']);
    }

    public function canBeViewedBy(User $user): bool
    {
        // Employee can view their own review
        if ($user->id === $this->employeeKpi->user_id) {
            return true;
        }

        // Reviewer can view
        if ($user->id === $this->reviewer_id) {
            return true;
        }

        // Admin/HRD can view all
        return $user->hasRole(['admin', 'hrd']);
    }

    public function canBeAcknowledgedBy(User $user): bool
    {
        return $user->id === $this->employeeKpi->user_id && 
               $this->review_status === 'completed' &&
               !$this->employee_acknowledged;
    }

    public function isOverdue(): bool
    {
        return $this->review_date->isPast() && 
               in_array($this->review_status, ['scheduled', 'in_progress']);
    }

    public function isComplete(): bool
    {
        return $this->review_status === 'completed' && $this->is_final;
    }

    // ===== HELPER METHODS =====

    public function getActionItems(): array
    {
        return $this->action_items ?? [];
    }

    public function addActionItem(string $item, ?Carbon $dueDate = null, string $priority = 'medium'): bool
    {
        $actionItems = $this->getActionItems();
        
        $actionItems[] = [
            'id' => uniqid(),
            'item' => $item,
            'due_date' => $dueDate?->toDateString(),
            'priority' => $priority,
            'status' => 'pending',
            'created_at' => now()->toISOString(),
        ];

        return $this->update(['action_items' => $actionItems]);
    }

    public function updateActionItemStatus(string $itemId, string $status): bool
    {
        $actionItems = $this->getActionItems();
        
        foreach ($actionItems as &$item) {
            if ($item['id'] === $itemId) {
                $item['status'] = $status;
                $item['updated_at'] = now()->toISOString();
                break;
            }
        }

        return $this->update(['action_items' => $actionItems]);
    }

    public function getGoalsForNextPeriod(): array
    {
        return $this->goals_for_next_period ?? [];
    }

    public function addGoal(string $goal, string $category = 'general', ?Carbon $targetDate = null): bool
    {
        $goals = $this->getGoalsForNextPeriod();
        
        $goals[] = [
            'id' => uniqid(),
            'goal' => $goal,
            'category' => $category,
            'target_date' => $targetDate?->toDateString(),
            'status' => 'active',
            'created_at' => now()->toISOString(),
        ];

        return $this->update(['goals_for_next_period' => $goals]);
    }

    public function getDaysUntilFollowup(): ?int
    {
        if (!$this->followup_date) {
            return null;
        }

        return now()->diffInDays($this->followup_date, false);
    }

    public function getReviewDuration(): ?string
    {
        if (!$this->review_duration_minutes) {
            return null;
        }

        $hours = intval($this->review_duration_minutes / 60);
        $minutes = $this->review_duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }

    public function generateReviewSummaryReport(): array
    {
        return [
            'employee' => $this->employeeKpi->user->name,
            'period' => $this->employeeKpi->period_name,
            'reviewer' => $this->reviewer->name,
            'review_date' => $this->review_date->format('d M Y'),
            'overall_rating' => $this->overall_manager_rating,
            'kpi_score' => $this->employeeKpi->overall_score,
            'assessments' => $this->overall_assessment,
            'strengths' => $this->strengths,
            'improvements' => $this->areas_for_improvement,
            'goals_count' => count($this->getGoalsForNextPeriod()),
            'action_items_count' => count($this->getActionItems()),
            'requires_followup' => $this->requires_followup,
            'employee_acknowledged' => $this->employee_acknowledged,
        ];
    }

    // ===== BOOT METHODS =====

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            // Set default review date if not provided
            if (!$review->review_date) {
                $review->review_date = now();
            }
        });

        static::updated(function ($review) {
            // Auto-mark KPI as reviewed when review is completed
            if ($review->isDirty('review_status') && $review->review_status === 'completed') {
                $review->employeeKpi->markAsReviewed($review->reviewer, 'Reviewed via formal review process');
            }

            // Log review completion
            if ($review->isDirty('review_status') && $review->review_status === 'completed') {
                \Log::info('KPI Review completed', [
                    'review_id' => $review->id,
                    'employee_kpi_id' => $review->employee_kpi_id,
                    'reviewer_id' => $review->reviewer_id,
                    'overall_rating' => $review->overall_manager_rating,
                ]);
            }
        });
    }
}