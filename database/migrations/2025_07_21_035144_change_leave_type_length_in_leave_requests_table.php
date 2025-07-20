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
            // Mengubah kolom 'leave_type' menjadi string dengan panjang 255 karakter
            $table->string('leave_type', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Ini adalah kebalikan dari perubahan di atas,
            // Anda bisa sesuaikan panjangnya kembali ke nilai semula jika perlu.
            // Namun, untuk amannya biarkan seperti ini.
            $table->string('leave_type', 255)->change();
        });
    }
};