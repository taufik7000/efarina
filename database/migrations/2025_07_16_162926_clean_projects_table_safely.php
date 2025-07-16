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
        // Step 1: Drop foreign keys first
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'redaksi_approved_by')) {
                $table->dropForeign(['redaksi_approved_by']);
            }
            if (Schema::hasColumn('projects', 'keuangan_approved_by')) {
                $table->dropForeign(['keuangan_approved_by']);
            }
        });

        // Step 2: Drop columns
        Schema::table('projects', function (Blueprint $table) {
            $columnsToDrop = [
                'proposal_budget', 
                'budget_items', 
                'proposal_description',
                'redaksi_approval_status',
                'redaksi_approved_by',
                'redaksi_approved_at',
                'redaksi_notes',
                'keuangan_approval_status',
                'keuangan_approved_by',
                'keuangan_approved_at',
                'keuangan_notes'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Step 3: Add new column
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'pengajuan_anggaran_id')) {
                $table->unsignedBigInteger('pengajuan_anggaran_id')->nullable()->after('deskripsi');
                $table->foreign('pengajuan_anggaran_id')->references('id')->on('pengajuan_anggarans')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'pengajuan_anggaran_id')) {
                $table->dropForeign(['pengajuan_anggaran_id']);
                $table->dropColumn('pengajuan_anggaran_id');
            }
        });
    }
};