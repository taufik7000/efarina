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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Cek jika kolom 'approved_by' BELUM ADA, baru tambahkan
            if (!Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users');
            }

            // Cek jika kolom 'action_at' BELUM ADA, baru tambahkan
            if (!Schema::hasColumn('leave_requests', 'action_at')) {
                $table->timestamp('action_at')->nullable()->after('status');
            }

            // Cek jika kolom 'rejection_reason' BELUM ADA, baru tambahkan
            if (!Schema::hasColumn('leave_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Cek jika kolom ADA, baru hapus
            if (Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('leave_requests', 'action_at')) {
                $table->dropColumn('action_at');
            }
            if (Schema::hasColumn('leave_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};