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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Jenis dokumen
            $table->enum('document_type', [
                'ktp',
                'cv', 
                'kontrak',
                'ijazah',
                'sertifikat',
                'foto',
                'other'
            ]);
            
            // Info file
            $table->string('file_path'); // Path lengkap file
            $table->string('file_name'); // Nama file asli
            $table->string('file_size')->nullable(); // Ukuran file dalam bytes
            $table->string('mime_type')->nullable(); // Type file (pdf, jpg, etc)
            
            // Metadata
            $table->text('description')->nullable(); // Deskripsi dokumen
            $table->timestamp('uploaded_at')->useCurrent();
            $table->boolean('is_verified')->default(false); // Apakah sudah diverifikasi HRD
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users'); // User yang verifikasi
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index('user_id');
            $table->index('document_type');
            $table->index(['user_id', 'document_type']);
            $table->index('is_verified');
            
            // Unique constraint untuk dokumen yang cuma boleh satu per user
            $table->unique(['user_id', 'document_type'], 'unique_user_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};