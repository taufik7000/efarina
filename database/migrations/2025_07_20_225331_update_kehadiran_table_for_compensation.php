<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            // Tambah kolom untuk referensi ke kompensasi
            $table->foreignId('compensation_id')->nullable()
                ->constrained('compensations')->onDelete('set null')
                ->comment('Referensi ke kompensasi jika tidak hadir karena ganti libur');
        });

        // Update enum status untuk include kompensasi
        DB::statement("ALTER TABLE kehadiran MODIFY COLUMN status ENUM('Tepat Waktu', 'Terlambat', 'Alfa', 'Cuti', 'Sakit', 'Izin', 'Kompensasi Libur') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['compensation_id']);
            $table->dropColumn(['compensation_id']);
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE kehadiran MODIFY COLUMN status ENUM('Tepat Waktu', 'Terlambat', 'Alfa', 'Cuti', 'Sakit', 'Izin') NOT NULL");
    }
};
