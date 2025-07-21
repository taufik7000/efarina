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
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            
            // Target scope & identification
            $table->enum('target_type', ['global', 'divisi', 'jabatan', 'individual'])
                  ->default('global')
                  ->comment('Scope target: global, divisi, jabatan, atau individual');
            $table->unsignedBigInteger('target_id')->nullable()
                  ->comment('ID dari divisi, jabatan, atau user tergantung target_type');
            $table->string('target_name')->nullable()
                  ->comment('Nama target untuk kemudahan identifikasi');
            
            // Period settings
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            
            // Attendance targets
            $table->decimal('min_attendance_rate', 5, 2)->default(95.00)
                  ->comment('Minimum attendance rate (%) - default 95%');
            $table->integer('max_late_days')->default(2)
                  ->comment('Maximum late days per period - default 2');
            $table->integer('max_absent_days')->default(1)
                  ->comment('Maximum absent days per period - default 1');
            
            // Task performance targets
            $table->integer('min_tasks_per_month')->default(10)
                  ->comment('Minimum tasks to be assigned per month - default 10');
            $table->decimal('min_completion_rate', 5, 2)->default(90.00)
                  ->comment('Minimum task completion rate (%) - default 90%');
            $table->integer('max_overdue_tasks')->default(1)
                  ->comment('Maximum overdue tasks allowed - default 1');
            $table->decimal('target_avg_completion_days', 5, 2)->default(3.00)
                  ->comment('Target average completion time in days - default 3');
            
            // Quality targets
            $table->decimal('min_quality_score', 5, 2)->default(80.00)
                  ->comment('Minimum quality score (0-100) - default 80');
            $table->decimal('target_client_satisfaction', 3, 2)->default(4.00)
                  ->comment('Target client satisfaction (1-5 scale) - default 4.0');
            $table->decimal('max_revision_rate', 5, 2)->default(20.00)
                  ->comment('Maximum revision rate (%) - default 20%');
            
            // Scoring weights (must total 100)
            $table->decimal('attendance_weight', 5, 2)->default(30.00)
                  ->comment('Weight for attendance score (%) - default 30%');
            $table->decimal('task_completion_weight', 5, 2)->default(40.00)
                  ->comment('Weight for task completion score (%) - default 40%');
            $table->decimal('quality_weight', 5, 2)->default(30.00)
                  ->comment('Weight for quality score (%) - default 30%');
            
            // Status & metadata
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable()
                  ->comment('Description atau notes untuk target ini');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['target_type', 'target_id', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};