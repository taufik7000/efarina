<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            // Perbaiki kolom document_type - perbesar ukuran dan pastikan bisa nullable
            $table->string('document_type', 100)->change(); // Dari 50 ke 100 karakter
            
            // Pastikan kolom lain juga cukup besar
            $table->string('file_name', 500)->change(); // Perbesar untuk nama file yang panjang
            $table->text('file_path')->change(); // Ubah ke text untuk path yang panjang
            
            // Tambah index untuk performa
            $table->index(['user_id', 'document_type'], 'idx_user_document_type');
            $table->index(['is_verified'], 'idx_is_verified');
            $table->index(['uploaded_at'], 'idx_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropIndex('idx_user_document_type');
            $table->dropIndex('idx_is_verified');
            $table->dropIndex('idx_uploaded_at');
            
            $table->string('document_type', 50)->change();
            $table->string('file_name', 255)->change();
            $table->string('file_path', 255)->change();
        });
    }
};