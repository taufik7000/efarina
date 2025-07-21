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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Data Personal
            $table->string('nik_ktp', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            
            // Kontak Darurat
            $table->string('kontak_darurat_nama')->nullable();
            $table->string('kontak_darurat_telp', 20)->nullable();
            
            // Data Finansial
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('npwp', 20)->nullable();
            
            // Catatan HRD
            $table->text('notes_hrd')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index('user_id');
            $table->index('nik_ktp');
            
            // Unique constraint
            $table->unique('user_id'); // Satu user hanya punya satu profile
            $table->unique('nik_ktp'); // NIK KTP harus unik
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};