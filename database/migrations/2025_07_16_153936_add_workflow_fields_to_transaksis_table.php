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
        Schema::table('transaksis', function (Blueprint $table) {
            // Tambah field untuk tracking workflow
            $table->string('workflow_type')->nullable()->after('project_id')
                  ->comment('Type: project_proposal, regular');
            $table->timestamp('redaksi_approved_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('redaksi_approved_by')->nullable()->after('redaksi_approved_at');
            $table->text('redaksi_notes')->nullable()->after('redaksi_approved_by');
            
            // Foreign key
            $table->foreign('redaksi_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropForeign(['redaksi_approved_by']);
            $table->dropColumn([
                'workflow_type',
                'redaksi_approved_at',
                'redaksi_approved_by',
                'redaksi_notes'
            ]);
        });
    }
};