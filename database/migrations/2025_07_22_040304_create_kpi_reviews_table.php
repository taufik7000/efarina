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
        Schema::create('kpi_reviews', function (Blueprint $table) {
            $table->id();
            
            // Review identification
            $table->foreignId('employee_kpi_id')->constrained()->onDelete('cascade')
                  ->comment('Reference to the KPI being reviewed');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade')
                  ->comment('Manager/HRD conducting the review');
            
            // Review type & timing
            $table->enum('review_type', ['monthly', 'quarterly', 'annual', 'special'])
                  ->default('monthly')
                  ->comment('Type of review');
            $table->date('review_date')
                  ->comment('Date when review was conducted');
            $table->enum('review_status', ['scheduled', 'in_progress', 'completed', 'cancelled'])
                  ->default('scheduled');
            
            // Performance assessment
            $table->decimal('attendance_assessment', 3, 2)->nullable()
                  ->comment('Manager assessment of attendance (1-5 scale)');
            $table->text('attendance_feedback')->nullable()
                  ->comment('Feedback on attendance performance');
            
            $table->decimal('task_performance_assessment', 3, 2)->nullable()
                  ->comment('Manager assessment of task performance (1-5 scale)');
            $table->text('task_performance_feedback')->nullable()
                  ->comment('Feedback on task performance');
            
            $table->decimal('quality_assessment', 3, 2)->nullable()
                  ->comment('Manager assessment of work quality (1-5 scale)');
            $table->text('quality_feedback')->nullable()
                  ->comment('Feedback on work quality');
            
            $table->decimal('collaboration_assessment', 3, 2)->nullable()
                  ->comment('Assessment of teamwork and collaboration (1-5 scale)');
            $table->text('collaboration_feedback')->nullable()
                  ->comment('Feedback on collaboration');
            
            $table->decimal('initiative_assessment', 3, 2)->nullable()
                  ->comment('Assessment of initiative and proactivity (1-5 scale)');
            $table->text('initiative_feedback')->nullable()
                  ->comment('Feedback on initiative');
            
            // Overall review
            $table->decimal('overall_manager_rating', 3, 2)->nullable()
                  ->comment('Overall manager rating (1-5 scale)');
            $table->text('strengths')->nullable()
                  ->comment('Employee strengths noted by manager');
            $table->text('areas_for_improvement')->nullable()
                  ->comment('Areas where employee can improve');
            $table->text('development_suggestions')->nullable()
                  ->comment('Suggestions for professional development');
            
            // Goals & action items
            $table->json('goals_for_next_period')->nullable()
                  ->comment('Goals set for next review period');
            $table->json('action_items')->nullable()
                  ->comment('Specific action items with deadlines');
            $table->text('support_needed')->nullable()
                  ->comment('Support/resources needed from management');
            
            // Employee input
            $table->text('employee_self_assessment')->nullable()
                  ->comment('Employee self-assessment notes');
            $table->text('employee_concerns')->nullable()
                  ->comment('Concerns raised by employee');
            $table->text('employee_suggestions')->nullable()
                  ->comment('Suggestions from employee');
            $table->boolean('employee_acknowledged')->default(false)
                  ->comment('Whether employee has acknowledged the review');
            $table->timestamp('employee_acknowledged_at')->nullable();
            
            // Follow-up
            $table->boolean('requires_followup')->default(false)
                  ->comment('Whether this review requires follow-up');
            $table->date('followup_date')->nullable()
                  ->comment('Scheduled follow-up date');
            $table->text('followup_notes')->nullable()
                  ->comment('Notes for follow-up');
            
            // Metadata
            $table->integer('review_duration_minutes')->nullable()
                  ->comment('Duration of review meeting in minutes');
            $table->json('attachments')->nullable()
                  ->comment('File attachments related to review');
            $table->boolean('is_final')->default(false)
                  ->comment('Whether this review is finalized');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_kpi_id', 'review_type']);
            $table->index(['reviewer_id', 'review_date']);
            $table->index(['review_status', 'requires_followup']);
            $table->index('review_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_reviews');
    }
};