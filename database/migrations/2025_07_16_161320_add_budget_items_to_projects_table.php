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
            // Hanya tambahkan kolom budget_items jika belum ada
            if (!Schema::hasColumn('projects', 'budget_items')) {
                $table->json('budget_items')->nullable()->after('proposal_budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'budget_items')) {
                $table->dropColumn('budget_items');
            }
        });
    }
};