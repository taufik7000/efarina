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
        // Check if table exists, if not create it
        if (!Schema::hasTable('employee_profiles')) {
            Schema::create('employee_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('nik_ktp', 20)->nullable()->unique();
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('agama', 50)->nullable();
                $table->enum('status_nikah', ['belum_menikah', 'menikah', 'cerai'])->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_telepon', 15)->nullable();
                $table->string('kontak_darurat_nama')->nullable();
                $table->string('kontak_darurat_telp', 15)->nullable();
                $table->string('kontak_darurat_hubungan', 100)->nullable();
                $table->decimal('gaji_pokok', 15, 2)->nullable();
                $table->string('no_rekening', 50)->nullable();
                $table->string('npwp', 20)->nullable();
                $table->text('notes_hrd')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('nik_ktp');
                $table->index('jenis_kelamin');
                $table->index('agama');
                $table->index('status_nikah');
            });
        } else {
            // If table exists, add missing columns
            Schema::table('employee_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_profiles', 'jenis_kelamin')) {
                    $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
                }
                
                if (!Schema::hasColumn('employee_profiles', 'agama')) {
                    $table->string('agama', 50)->nullable()->after('jenis_kelamin');
                }
                
                if (!Schema::hasColumn('employee_profiles', 'status_nikah')) {
                    $table->enum('status_nikah', ['belum_menikah', 'menikah', 'cerai'])->nullable()->after('agama');
                }
                
                if (!Schema::hasColumn('employee_profiles', 'no_telepon')) {
                    $table->string('no_telepon', 15)->nullable()->after('alamat');
                }
                
                if (!Schema::hasColumn('employee_profiles', 'kontak_darurat_hubungan')) {
                    $table->string('kontak_darurat_hubungan', 100)->nullable()->after('kontak_darurat_telp');
                }
                
                if (!Schema::hasColumn('employee_profiles', 'notes_hrd')) {
                    $table->text('notes_hrd')->nullable()->after('npwp');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};