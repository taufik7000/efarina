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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('nama_task');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'blocked'])->default('todo');
            $table->enum('prioritas', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_deadline')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->integer('order_index')->default(0);
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('tanggal_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};