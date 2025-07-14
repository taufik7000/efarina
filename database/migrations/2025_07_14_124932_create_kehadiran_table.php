<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Membuat tabel 'kehadiran' untuk menyimpan data absensi
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel 'users'
            // Jika pengguna dihapus, data kehadirannya juga akan terhapus
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal'); // Tanggal absensi
            $table->time('jam_masuk')->nullable(); // Waktu saat check-in
            $table->time('jam_pulang')->nullable(); // Waktu saat check-out
            $table->string('lokasi_masuk')->nullable(); // Koordinat GPS saat masuk
            $table->string('lokasi_pulang')->nullable(); // Koordinat GPS saat pulang
            $table->string('foto_masuk')->nullable(); // Path/URL foto selfie saat masuk
            $table->string('foto_pulang')->nullable(); // Path/URL foto selfie saat pulang
            $table->string('metode_absen')->nullable(); // Metode absensi (misal: 'gps', 'qrcode')
            // Status kehadiran: Tepat Waktu, Terlambat, Absen, Hadir
            $table->enum('status', ['Tepat Waktu', 'Terlambat', 'Absen', 'Hadir'])->default('Absen');
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};