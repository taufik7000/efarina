<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            // Update enum status untuk include cuti
            $table->enum('status', [
                'Tepat Waktu', 'Terlambat', 'Alfa', 'Cuti', 'Sakit', 'Izin'
            ])->change();
            
            // Tambah kolom untuk referensi ke leave request
            $table->foreignId('leave_request_id')->nullable()
                ->constrained('leave_requests')->onDelete('set null')
                ->comment('Referensi ke pengajuan cuti jika status = Cuti');
                
            // Tambah kolom untuk keterangan khusus
            $table->text('notes')->nullable()->comment('Catatan khusus (misal: sakit, izin mendadak)');
        });
    }

    public function down(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
            $table->dropColumn(['leave_request_id', 'notes']);
            
            // Revert status enum ke yang lama
            $table->enum('status', ['Tepat Waktu', 'Terlambat'])->change();
        });
    }
};