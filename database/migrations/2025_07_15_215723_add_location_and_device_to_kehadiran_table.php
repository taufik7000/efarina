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
    Schema::table('kehadiran', function (Blueprint $table) {
        // Cek sebelum menambahkan setiap kolom
        if (!Schema::hasColumn('kehadiran', 'lokasi_masuk')) {
            $table->string('lokasi_masuk')->nullable()->after('foto_masuk');
        }
        if (!Schema::hasColumn('kehadiran', 'lokasi_pulang')) {
            $table->string('lokasi_pulang')->nullable()->after('foto_pulang');
        }
        if (!Schema::hasColumn('kehadiran', 'info_perangkat_masuk')) {
            $table->text('info_perangkat_masuk')->nullable()->after('lokasi_masuk');
        }
        if (!Schema::hasColumn('kehadiran', 'info_perangkat_pulang')) {
            $table->text('info_perangkat_pulang')->nullable()->after('lokasi_pulang');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            //
        });
    }
};
