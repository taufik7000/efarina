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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nama_project');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'cancelled'])->default('draft');
            $table->enum('prioritas', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_deadline')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->foreignId('project_manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('divisi_id')->nullable()->constrained('divisis')->onDelete('set null');
            $table->decimal('budget', 15, 2)->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->json('team_members')->nullable(); // Array of user IDs
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'prioritas']);
            $table->index('tanggal_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};