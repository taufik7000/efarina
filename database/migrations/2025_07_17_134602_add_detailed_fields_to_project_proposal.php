<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_proposals', function (Blueprint $table) {
            // Detailed proposal fields
            $table->text('tujuan_utama')->after('tujuan_project');
            $table->text('target_audience')->after('tujuan_utama');
            $table->json('target_metrics')->nullable()->after('target_audience');
            $table->json('deliverables')->nullable()->after('target_metrics');
            
            // File attachments
            $table->json('attachments')->nullable()->after('deliverables');
            
            // Additional details
            $table->text('metodologi')->nullable()->after('attachments');
            $table->text('resiko_dan_mitigasi')->nullable()->after('metodologi');
            $table->json('timeline_detail')->nullable()->after('resiko_dan_mitigasi');
            
            // Budget breakdown
            $table->json('budget_breakdown')->nullable()->after('estimasi_budget');
        });
    }

    public function down(): void
    {
        Schema::table('project_proposals', function (Blueprint $table) {
            $table->dropColumn([
                'tujuan_utama',
                'target_audience', 
                'target_metrics',
                'deliverables',
                'attachments',
                'metodologi',
                'resiko_dan_mitigasi',
                'timeline_detail',
                'budget_breakdown'
            ]);
        });
    }
};