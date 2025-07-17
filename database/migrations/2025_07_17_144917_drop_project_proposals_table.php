<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key constraint dari projects table dulu
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['project_proposal_id']);
            $table->dropIndex(['project_proposal_id']);
            $table->dropColumn('project_proposal_id');
        });

        // Drop project_proposals table
        Schema::dropIfExists('project_proposals');
    }

    public function down(): void
    {
        // Recreate project_proposals table (simplified version)
        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('judul_proposal');
            $table->text('deskripsi');
            $table->enum('kategori', ['content', 'event', 'campaign', 'research', 'other']);
            $table->enum('prioritas', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Re-add foreign key ke projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('project_proposal_id')->nullable()->after('id');
            $table->foreign('project_proposal_id')->references('id')->on('project_proposals')->onDelete('set null');
            $table->index('project_proposal_id');
        });
    }
};