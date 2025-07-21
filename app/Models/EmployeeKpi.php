<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EmployeeKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_year',
        'period_month',
        'kpi_target_id',
        'attendance_score',
        'task_completion_score',
        'quality_score',
        'overall_score',
        'total_working_days',
        'present_days',
        'on_time_days',
        'late_days',
        'absent_days',
        'leave_days',
        'attendance_rate',
        'total_tasks_assigned',
        'tasks_completed',
        'tasks_overdue',
        'tasks_completed_on_time',
        'task_completion_rate',
        'on_time_completion_rate',
        'average_task_completion_time',
        'average_task_rating',
        'total_revisions',
        'revision_rate',
        'client_satisfaction_avg',
        'status',
        'comments',
        'employee_notes',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'calculated_at',
        'calculation_details',
        'is_final',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'calculated_at' => 'datetime',
        'attendance_score' => 'decimal:2',
        'task_completion_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
        'task_completion_rate' => 'decimal:2',
        'on_time_completion_rate' => 'decimal:2',
        'average_task_completion_time' => 'decimal:2',
        'average_task_rating' => 'decimal:2',
        'revision_rate' => 'decimal:2',
        'client_satisfaction_avg' => 'decimal:2',
        'calculation_details' => 'array',
        'is_final' => 'boolean',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(KpiReview::class);
    }

    // ===== ACCESSORS =====

    public function getPeriodNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$this->period_month] . ' ' . $this->period_year;
    }

    public function getOverallGradeAttribute(): string
    {
        $score = $this->overall_score;
        
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E'
        };
    }

    public function getOverallGradeColorAttribute(): string
    {
        return match($this->overall_grade) {
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D' => 'danger',
            'E' => 'danger',
            default => 'gray'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'calculated' => 'info',
            'reviewed' => 'warning',
            'approved' => 'success',
            'disputed' => 'danger',
            default => 'gray'
        };
    }

    public function getPerformanceLevelAttribute(): string
    {
        return match($this->overall_grade) {
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Average',
            'D' => 'Below Average',
            'E' => 'Poor',
            default => 'Not Rated'
        };
    }

    // ===== SCOPES =====

    public function scopeForPeriod($query, int $year, ?int $month = null)
    {
        $query->where('period_year', $year);
        
        if ($month) {
            $query->where('period_month', $month);
        }
        
        return $query;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingReview($query)
    {
        return $query->whereIn('status', ['calculated', 'reviewed']);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeByGrade($query, string $grade)
    {
        $ranges = [
            'A' => [90, 100],
            'B' => [80, 89.99],
            'C' => [70, 79.99],
            'D' => [60, 69.99],
            'E' => [0, 59.99],
        ];
        
        if (isset($ranges[$grade])) {
            [$min, $max] = $ranges[$grade];
            $query->whereBetween('overall_score', [$min, $max]);
        }
        
        return $query;
    }

    public function scopeTopPerformers($query, int $limit = 10)
    {
        return $query->orderBy('overall_score', 'desc')->limit($limit);
    }

    public function scopeBottomPerformers($query, int $limit = 10)
    {
        return $query->orderBy('overall_score', 'asc')->limit($limit);
    }

    // ===== STATIC CALCULATION METHODS =====

    /**
     * Calculate and store KPI for a user in specific period
     */
    public static function calculateKpi(User $user, int $year, int $month): self
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get applicable target
        $target = KpiTarget::getEffectiveTargetForUser($user, $startDate);
        $kpiTarget = KpiTarget::getApplicableTarget($user, $startDate);
        
        // Calculate metrics
        $attendanceData = self::calculateAttendanceMetrics($user, $startDate, $endDate);
        $taskData = self::calculateTaskMetrics($user, $startDate, $endDate);
        $qualityData = self::calculateQualityMetrics($user, $startDate, $endDate);
        
        // Calculate scores
        $attendanceScore = self::calculateAttendanceScore($attendanceData, $target);
        $taskCompletionScore = self::calculateTaskCompletionScore($taskData, $target);
        $qualityScore = self::calculateQualityScore($qualityData, $target);
        
        // Calculate overall score (weighted average)
        $overallScore = (
            ($attendanceScore * $target['attendance_weight']) +
            ($taskCompletionScore * $target['task_completion_weight']) +
            ($qualityScore * $target['quality_weight'])
        ) / 100;
        
        // Prepare calculation details for audit
        $calculationDetails = [
            'target_used' => $target,
            'attendance_data' => $attendanceData,
            'task_data' => $taskData,
            'quality_data' => $qualityData,
            'scores' => [
                'attendance' => $attendanceScore,
                'task_completion' => $taskCompletionScore,
                'quality' => $qualityScore,
                'overall' => $overallScore,
            ],
            'calculated_at' => now()->toISOString(),
        ];
        
        // Create or update KPI record
        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_year' => $year,
                'period_month' => $month,
            ],
            array_merge(
                $attendanceData,
                $taskData,
                $qualityData,
                [
                    'kpi_target_id' => $kpiTarget?->id,
                    'attendance_score' => $attendanceScore,
                    'task_completion_score' => $taskCompletionScore,
                    'quality_score' => $qualityScore,
                    'overall_score' => $overallScore,
                    'status' => 'calculated',
                    'calculated_at' => now(),
                    'calculation_details' => $calculationDetails,
                ]
            )
        );
    }

    /**
     * Calculate attendance metrics for a period
     */
    private static function calculateAttendanceMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $totalWorkingDays = 0;
        $presentDays = 0;
        $onTimeDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            // Skip Sundays (assuming Sunday is day off)
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                $totalWorkingDays++;
                
                $attendance = $user->kehadiran()
                    ->whereDate('tanggal', $current)
                    ->first();
                
                if ($attendance) {
                    switch ($attendance->status) {
                        case 'Tepat Waktu':
                            $presentDays++;
                            $onTimeDays++;
                            break;
                        case 'Terlambat':
                            $presentDays++;
                            $lateDays++;
                            break;
                        case 'Alfa':
                            $absentDays++;
                            break;
                        case 'Cuti':
                        case 'Sakit':
                        case 'Izin':
                        case 'Kompensasi Libur':
                            $leaveDays++;
                            break;
                    }
                } else {
                    $absentDays++;
                }
            }
            $current->addDay();
        }
        
        $attendanceRate = $totalWorkingDays > 0 ? 
            round(($presentDays / $totalWorkingDays) * 100, 2) : 0;
        
        return [
            'total_working_days' => $totalWorkingDays,
            'present_days' => $presentDays,
            'on_time_days' => $onTimeDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Calculate task metrics for a period
     */
    private static function calculateTaskMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $assignedTasks = $user->assignedTasks()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $totalAssigned = $assignedTasks->count();
        $completed = $assignedTasks->where('status', 'done')->count();
        
        $overdue = $assignedTasks->filter(function ($task) {
            return $task->tanggal_deadline && 
                   Carbon::parse($task->tanggal_deadline)->isPast() && 
                   $task->status !== 'done';
        })->count();
        
        $completedOnTime = $assignedTasks->filter(function ($task) {
            return $task->tanggal_selesai && 
                   $task->tanggal_deadline &&
                   Carbon::parse($task->tanggal_selesai)->lte(Carbon::parse($task->tanggal_deadline));
        })->count();
        
        // Calculate average completion time
        $completedTasksWithDates = $assignedTasks->filter(function ($task) {
            return $task->status === 'done' && 
                   $task->tanggal_mulai && 
                   $task->tanggal_selesai;
        });
        
        $avgCompletionTime = 0;
        if ($completedTasksWithDates->count() > 0) {
            $totalDays = $completedTasksWithDates->sum(function ($task) {
                return Carbon::parse($task->tanggal_mulai)
                    ->diffInDays(Carbon::parse($task->tanggal_selesai));
            });
            $avgCompletionTime = round($totalDays / $completedTasksWithDates->count(), 2);
        }
        
        $completionRate = $totalAssigned > 0 ? 
            round(($completed / $totalAssigned) * 100, 2) : 0;
        
        $onTimeCompletionRate = $completed > 0 ? 
            round(($completedOnTime / $completed) * 100, 2) : 0;
        
        return [
            'total_tasks_assigned' => $totalAssigned,
            'tasks_completed' => $completed,
            'tasks_overdue' => $overdue,
            'tasks_completed_on_time' => $completedOnTime,
            'task_completion_rate' => $completionRate,
            'on_time_completion_rate' => $onTimeCompletionRate,
            'average_task_completion_time' => $avgCompletionTime,
        ];
    }

    /**
     * Calculate quality metrics for a period
     */
    private static function calculateQualityMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        // For now, return default values
        // These can be enhanced with actual quality tracking
        return [
            'average_task_rating' => 4.0,
            'total_revisions' => 0,
            'revision_rate' => 0.0,
            'client_satisfaction_avg' => 4.0,
        ];
    }

    /**
     * Calculate attendance score based on metrics and targets
     */
    private static function calculateAttendanceScore(array $metrics, array $target): float
    {
        $baseScore = $metrics['attendance_rate'];
        
        // Apply penalties for late and absent days
        $latePenalty = $metrics['late_days'] > $target['max_late_days'] ? 
            ($metrics['late_days'] - $target['max_late_days']) * 2 : 0;
        
        $absentPenalty = $metrics['absent_days'] > $target['max_absent_days'] ? 
            ($metrics['absent_days'] - $target['max_absent_days']) * 5 : 0;
        
        $finalScore = max(0, $baseScore - $latePenalty - $absentPenalty);
        
        return round($finalScore, 2);
    }

    /**
     * Calculate task completion score based on metrics and targets
     */
    private static function calculateTaskCompletionScore(array $metrics, array $target): float
    {
        // Base score from completion rate
        $baseScore = $metrics['task_completion_rate'];
        
        // Bonus for on-time completion
        $onTimeBonus = $metrics['on_time_completion_rate'] >= $target['min_completion_rate'] ? 
            10 : ($metrics['on_time_completion_rate'] * 0.1);
        
        // Penalty for overdue tasks
        $overduePenalty = $metrics['tasks_overdue'] > $target['max_overdue_tasks'] ? 
            ($metrics['tasks_overdue'] - $target['max_overdue_tasks']) * 5 : 0;
        
        // Bonus/penalty for task volume vs target
        $volumeMultiplier = $metrics['total_tasks_assigned'] >= $target['min_tasks_per_month'] ? 
            1.0 : 0.9;
        
        $finalScore = min(100, ($baseScore + $onTimeBonus - $overduePenalty) * $volumeMultiplier);
        
        return round(max(0, $finalScore), 2);
    }

    /**
     * Calculate quality score based on metrics and targets
     */
    private static function calculateQualityScore(array $metrics, array $target): float
    {
        // For now, return a score based on minimum quality target
        // This can be enhanced with actual quality metrics
        $score = ($metrics['average_task_rating'] / 5) * 100;
        
        return round(min(100, max(0, $score)), 2);
    }

    // ===== WORKFLOW METHODS =====

    public function markAsReviewed(User $reviewer, ?string $comments = null): bool
    {
        return $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'comments' => $comments,
        ]);
    }

    public function approve(User $approver, ?string $comments = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'comments' => $comments,
            'is_final' => true,
        ]);
    }

    public function dispute(User $user, string $reason): bool
    {
        return $this->update([
            'status' => 'disputed',
            'employee_notes' => $reason,
        ]);
    }

    // ===== VALIDATION METHODS =====

    public function canBeEditedBy(User $user): bool
    {
        // Only allow editing if not finalized and user has permission
        if ($this->is_final) {
            return false;
        }
        
        return $user->hasRole(['admin', 'hrd']) || 
               $user->id === $this->user_id;
    }

    public function canBeApprovedBy(User $user): bool
    {
        return $user->hasRole(['admin', 'hrd']) && 
               $this->status === 'reviewed';
    }

    public function canBeDisputedBy(User $user): bool
    {
        return $user->id === $this->user_id && 
               in_array($this->status, ['calculated', 'reviewed']);
    }

    // ===== HELPER METHODS =====

    public function getScoreBreakdown(): array
    {
        return [
            'attendance' => [
                'score' => $this->attendance_score,
                'weight' => $this->kpiTarget?->attendance_weight ?? 30,
                'contribution' => ($this->attendance_score * ($this->kpiTarget?->attendance_weight ?? 30)) / 100,
            ],
            'task_completion' => [
                'score' => $this->task_completion_score,
                'weight' => $this->kpiTarget?->task_completion_weight ?? 40,
                'contribution' => ($this->task_completion_score * ($this->kpiTarget?->task_completion_weight ?? 40)) / 100,
            ],
            'quality' => [
                'score' => $this->quality_score,
                'weight' => $this->kpiTarget?->quality_weight ?? 30,
                'contribution' => ($this->quality_score * ($this->kpiTarget?->quality_weight ?? 30)) / 100,
            ],
        ];
    }

    public function getComparisonWithTarget(): array
    {
        $target = $this->kpiTarget ? 
            KpiTarget::getEffectiveTargetForUser($this->user, Carbon::create($this->period_year, $this->period_month)) :
            KpiTarget::getDefaultTargetValues();

        return [
            'attendance_rate' => [
                'actual' => $this->attendance_rate,
                'target' => $target['min_attendance_rate'],
                'status' => $this->attendance_rate >= $target['min_attendance_rate'] ? 'met' : 'not_met',
            ],
            'task_completion_rate' => [
                'actual' => $this->task_completion_rate,
                'target' => $target['min_completion_rate'],
                'status' => $this->task_completion_rate >= $target['min_completion_rate'] ? 'met' : 'not_met',
            ],
            'tasks_assigned' => [
                'actual' => $this->total_tasks_assigned,
                'target' => $target['min_tasks_per_month'],
                'status' => $this->total_tasks_assigned >= $target['min_tasks_per_month'] ? 'met' : 'not_met',
            ],
        ];
    }

    // ===== STATIC UTILITY METHODS =====

    /**
     * Get team performance summary for a period
     */
    public static function getTeamSummary(int $year, int $month, ?array $userIds = null): array
    {
        $query = self::forPeriod($year, $month);
        
        if ($userIds) {
            $query->whereIn('user_id', $userIds);
        }
        
        $kpis = $query->get();
        
        return [
            'total_employees' => $kpis->count(),
            'average_overall_score' => round($kpis->avg('overall_score'), 2),
            'grade_distribution' => [
                'A' => $kpis->filter(fn($kpi) => $kpi->overall_grade === 'A')->count(),
                'B' => $kpis->filter(fn($kpi) => $kpi->overall_grade === 'B')->count(),
                'C' => $kpis->filter(fn($kpi) => $kpi->overall_grade === 'C')->count(),
                'D' => $kpis->filter(fn($kpi) => $kpi->overall_grade === 'D')->count(),
                'E' => $kpis->filter(fn($kpi) => $kpi->overall_grade === 'E')->count(),
            ],
            'top_performers' => $kpis->sortByDesc('overall_score')->take(5)->values(),
            'needs_attention' => $kpis->where('overall_score', '<', 70)->sortBy('overall_score')->values(),
        ];
    }

    /**
     * Get employee trends over multiple periods
     */
    public static function getEmployeeTrends(User $user, int $months = 6): array
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths($months - 1)->startOfMonth();
        
        $kpis = self::where('user_id', $user->id)
                   ->where('period_year', '>=', $startDate->year)
                   ->where(function($q) use ($startDate, $endDate) {
                       $q->where('period_year', '>', $startDate->year)
                         ->orWhere(function($q2) use ($startDate, $endDate) {
                             $q2->where('period_year', $startDate->year)
                                ->where('period_month', '>=', $startDate->month);
                         });
                   })
                   ->where('period_year', '<=', $endDate->year)
                   ->where(function($q) use ($endDate) {
                       $q->where('period_year', '<', $endDate->year)
                         ->orWhere(function($q2) use ($endDate) {
                             $q2->where('period_year', $endDate->year)
                                ->where('period_month', '<=', $endDate->month);
                         });
                   })
                   ->orderBy('period_year')
                   ->orderBy('period_month')
                   ->get();
        
        return [
            'periods' => $kpis->map(fn($kpi) => $kpi->period_name)->toArray(),
            'overall_scores' => $kpis->pluck('overall_score')->toArray(),
            'attendance_scores' => $kpis->pluck('attendance_score')->toArray(),
            'task_completion_scores' => $kpis->pluck('task_completion_score')->toArray(),
            'quality_scores' => $kpis->pluck('quality_score')->toArray(),
            'trend_direction' => self::calculateTrendDirection($kpis->pluck('overall_score')->toArray()),
        ];
    }

    /**
     * Calculate trend direction from scores array
     */
    private static function calculateTrendDirection(array $scores): string
    {
        if (count($scores) < 2) {
            return 'stable';
        }
        
        $recent = array_slice($scores, -3); // Last 3 months
        $older = array_slice($scores, 0, -3);
        
        if (empty($older)) {
            return 'stable';
        }
        
        $recentAvg = array_sum($recent) / count($recent);
        $olderAvg = array_sum($older) / count($older);
        
        $difference = $recentAvg - $olderAvg;
        
        if ($difference > 5) {
            return 'improving';
        } elseif ($difference < -5) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    // ===== BOOT METHODS =====

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kpi) {
            // Auto-set calculated_at if not set
            if (!$kpi->calculated_at) {
                $kpi->calculated_at = now();
            }
        });
        
        static::updated(function ($kpi) {
            // Log status changes for audit
            if ($kpi->isDirty('status')) {
                \Log::info('KPI status changed', [
                    'kpi_id' => $kpi->id,
                    'user_id' => $kpi->user_id,
                    'period' => $kpi->period_name,
                    'old_status' => $kpi->getOriginal('status'),
                    'new_status' => $kpi->status,
                    'changed_by' => auth()->id(),
                ]);
            }
        });
    }
}