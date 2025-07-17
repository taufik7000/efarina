<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add reference to project proposal (optional)
            $table->unsignedBigInteger('project_proposal_id')->nullable()->after('id');
            
            // Add target and metrics fields
            $table->text('tujuan_utama')->nullable()->after('deskripsi');
            $table->text('target_audience')->nullable()->after('tujuan_utama');
            $table->json('target_metrics')->nullable()->after('target_audience');
            $table->json('deliverables')->nullable()->after('target_metrics');
            $table->text('expected_outcomes')->nullable()->after('deliverables');
            
            // Add foreign key constraint
            $table->foreign('project_proposal_id')
                  ->references('id')
                  ->on('project_proposals')
                  ->onDelete('set null');
                  
            // Add index for better performance
            $table->index('project_proposal_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['project_proposal_id']);
            $table->dropIndex(['project_proposal_id']);
            $table->dropColumn([
                'project_proposal_id',
                'tujuan_utama',
                'target_audience',
                'target_metrics',
                'deliverables',
                'expected_outcomes'
            ]);
        });
    }
};