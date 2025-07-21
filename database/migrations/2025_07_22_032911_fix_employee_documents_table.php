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
        // Check if table exists, if not create it
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('document_type');
                $table->string('file_path');
                $table->string('file_name');
                $table->bigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->text('description')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('verification_notes')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['user_id', 'document_type']);
                $table->index('is_verified');
            });
        } else {
            // If table exists, add missing columns
            Schema::table('employee_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_documents', 'verification_notes')) {
                    $table->text('verification_notes')->nullable()->after('verified_by');
                }
                
                if (!Schema::hasColumn('employee_documents', 'uploaded_at')) {
                    $table->timestamp('uploaded_at')->nullable()->after('description');
                }
                
                if (!Schema::hasColumn('employee_documents', 'file_size')) {
                    $table->bigInteger('file_size')->nullable()->after('file_name');
                }
                
                if (!Schema::hasColumn('employee_documents', 'mime_type')) {
                    $table->string('mime_type')->nullable()->after('file_size');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};