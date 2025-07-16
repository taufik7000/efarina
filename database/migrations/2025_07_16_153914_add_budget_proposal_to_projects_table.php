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
        Schema::table('projects', function (Blueprint $table) {
            // Proposal Budget Fields
            $table->decimal('proposal_budget', 15, 2)->nullable()->after('deskripsi');
            $table->text('proposal_description')->nullable()->after('proposal_budget');
            
            // Redaksi Approval Fields
            $table->enum('redaksi_approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('proposal_description');
            $table->unsignedBigInteger('redaksi_approved_by')->nullable()->after('redaksi_approval_status');
            $table->timestamp('redaksi_approved_at')->nullable()->after('redaksi_approved_by');
            $table->text('redaksi_notes')->nullable()->after('redaksi_approved_at');
            
            // Keuangan Approval Fields
            $table->enum('keuangan_approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('redaksi_notes');
            $table->unsignedBigInteger('keuangan_approved_by')->nullable()->after('keuangan_approval_status');
            $table->timestamp('keuangan_approved_at')->nullable()->after('keuangan_approved_by');
            $table->text('keuangan_notes')->nullable()->after('keuangan_approved_at');
            
            // Foreign Keys
            $table->foreign('redaksi_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('keuangan_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['redaksi_approved_by']);
            $table->dropForeign(['keuangan_approved_by']);
            
            $table->dropColumn([
                'proposal_budget',
                'proposal_description',
                'redaksi_approval_status',
                'redaksi_approved_by',
                'redaksi_approved_at',
                'redaksi_notes',
                'keuangan_approval_status',
                'keuangan_approved_by',
                'keuangan_approved_at',
                'keuangan_notes'
            ]);
        });
    }
};