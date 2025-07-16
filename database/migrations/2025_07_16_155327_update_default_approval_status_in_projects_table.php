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
            // Update default value untuk kolom yang sudah ada
            $table->enum('redaksi_approval_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->change();
            
            $table->enum('keuangan_approval_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada perubahan yang perlu di-reverse
    }
};