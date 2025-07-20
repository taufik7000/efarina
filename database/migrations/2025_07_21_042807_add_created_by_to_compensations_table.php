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
        Schema::table('compensations', function (Blueprint $table) {
            // Check if the column does NOT exist, then add it.
            if (!Schema::hasColumn('compensations', 'created_by')) {
                // This column will store the ID of the HRD user who approved the leave
                // that generated this compensation.
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compensations', function (Blueprint $table) {
            // Check if the column EXISTS, then drop it.
            if (Schema::hasColumn('compensations', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};