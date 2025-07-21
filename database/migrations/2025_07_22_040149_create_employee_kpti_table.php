<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            
            // Employee & period identification
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('period_year');
            $table->tinyInteger('period_month')->comment('1-12 for month');
            $table->foreignId('kpi_target_id')->nullable()->constrained()->onDelete('set null')
                  ->comment('Reference to the KPI target used for calculation');
            
            // Calculated scores (0-100)
            $table->decimal('attendance_score', 5, 2)->default(0)
                  ->comment('Attendance performance score (0-100)');
            $table->decimal('task_completion_score', 5, 2)->default(0)
                  ->comment('Task completion performance score (0-100)');
            $table->decimal('quality_score', 5, 2)->default(0)
                  ->comment('Quality performance score (0-100)');
            $table->decimal('overall_score', 5, 2)->default(0)
                  ->comment('Overall KPI score (weighted average)');
            
            // Attendance metrics (raw data)
            $table->integer('total_working_days')->default(0)
                  ->comment('Total working days in the period');
            $table->integer('present_days')->default(0)
                  ->comment('Days present (on time + late)');
            $table->integer('on_time_days')->default(0)
                  ->comment('Days present on time');
            $table->integer('late_days')->default(0)
                  ->comment('Days late');
            $table->integer('absent_days')->default(0)
                  ->comment('Days absent (alfa)');
            $table->integer('leave_days')->default(0)
                  ->comment('Days on leave (cuti/izin/sakit)');
            $table->decimal('attendance_rate', 5, 2)->default(0)
                  ->comment('Attendance rate percentage');
            
            // Task performance metrics (raw data)
            $table->integer('total_tasks_assigned')->default(0)
                  ->comment('Total tasks assigned in period');
            $table->integer('tasks_completed')->default(0)
                  ->comment('Tasks completed');
            $table->integer('tasks_overdue')->default(0)
                  ->comment('Tasks that are overdue');
            $table->integer('tasks_completed_on_time')->default(0)
                  ->comment('Tasks completed before/on deadline');
            $table->decimal('task_completion_rate', 5, 2)->default(0)
                  ->comment('Task completion rate percentage');
            $table->decimal('on_time_completion_rate', 5, 2)->default(0)
                  ->comment('On-time completion rate percentage');
            $table->decimal('average_task_completion_time', 8, 2)->default(0)
                  ->comment('Average completion time in days');
            
            // Quality metrics (raw data)
            $table->decimal('average_task_rating', 3, 2)->default(0)
                  ->comment('Average task rating (1-5 scale)');
            $table->integer('total_revisions')->default(0)
                  ->comment('Total revisions requested');
            $table->decimal('revision_rate', 5, 2)->default(0)
                  ->comment('Revision rate percentage');
            $table->decimal('client_satisfaction_avg', 3, 2)->default(0)
                  ->comment('Average client satisfaction (1-5 scale)');
            
            // Review & approval
            $table->enum('status', ['draft', 'calculated', 'reviewed', 'approved', 'disputed'])
                  ->default('draft')
                  ->comment('KPI status in approval workflow');
            $table->text('comments')->nullable()
                  ->comment('Comments from manager/HRD');
            $table->text('employee_notes')->nullable()
                  ->comment('Notes/response from employee');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')
                  ->comment('Manager/HRD who reviewed this KPI');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')
                  ->comment('Final approver (usually HRD)');
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->timestamp('calculated_at')->nullable()
                  ->comment('When the KPI was calculated');
            $table->json('calculation_details')->nullable()
                  ->comment('Detailed breakdown of calculation for audit');
            $table->boolean('is_final')->default(false)
                  ->comment('Whether this KPI is finalized (no more changes)');
            
            $table->timestamps();
            
            // Constraints & indexes
            $table->unique(['user_id', 'period_year', 'period_month'], 'unique_user_period');
            $table->index(['period_year', 'period_month']);
            $table->index(['status', 'is_final']);
            $table->index('overall_score');
            $table->index(['user_id', 'period_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpis');
    }
};