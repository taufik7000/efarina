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
        Schema::create('kpi_target_overrides', function (Blueprint $table) {
            $table->id();
            
            // Target override identification
            $table->foreignId('user_id')->constrained()->onDelete('cascade')
                  ->comment('Employee who gets the override');
            $table->foreignId('kpi_target_id')->constrained()->onDelete('cascade')
                  ->comment('Base target that is being overridden');
            
            // Override details
            $table->string('field_name')
                  ->comment('Field being overridden (e.g., min_tasks_per_month, min_attendance_rate)');
            $table->decimal('override_value', 10, 2)
                  ->comment('New value for the field');
            $table->decimal('original_value', 10, 2)->nullable()
                  ->comment('Original value from base target (for audit)');
            
            // Override justification
            $table->text('reason')
                  ->comment('Reason for override (e.g., "Senior role", "Part-time", "Medical condition")');
            $table->enum('override_type', ['increase', 'decrease', 'custom'])
                  ->default('custom')
                  ->comment('Type of override for categorization');
            
            // Validity period
            $table->date('effective_from')
                  ->comment('When this override becomes effective');
            $table->date('effective_until')->nullable()
                  ->comment('When this override expires (NULL = permanent)');
            
            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])
                  ->default('pending')
                  ->comment('Approval status');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade')
                  ->comment('Who requested this override (usually HRD/Manager)');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')
                  ->comment('Who approved this override');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable()
                  ->comment('Notes from approver');
            
            // Metadata
            $table->boolean('is_active')->default(true)
                  ->comment('Whether this override is currently active');
            $table->json('additional_data')->nullable()
                  ->comment('Any additional context or metadata');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'kpi_target_id', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
            $table->index(['status', 'is_active']);
            $table->index('field_name');
            
            // Ensure no duplicate overrides for same field in overlapping periods
            $table->unique(['user_id', 'kpi_target_id', 'field_name', 'effective_from'], 'unique_override_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_target_overrides');
    }
};