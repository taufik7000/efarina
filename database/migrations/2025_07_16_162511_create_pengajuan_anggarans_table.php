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
        Schema::create('pengajuan_anggarans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->string('judul_pengajuan');
            $table->text('deskripsi');
            $table->decimal('total_anggaran', 15, 2);
            $table->json('detail_items')->nullable();
            $table->enum('kategori', ['project', 'operasional', 'investasi', 'lainnya'])->default('project');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_dibutuhkan');
            $table->text('justifikasi');
            
            // Status Approval
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->enum('redaksi_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('redaksi_approved_by')->nullable();
            $table->timestamp('redaksi_approved_at')->nullable();
            $table->text('redaksi_notes')->nullable();
            
            $table->enum('keuangan_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('keuangan_approved_by')->nullable();
            $table->timestamp('keuangan_approved_at')->nullable();
            $table->text('keuangan_notes')->nullable();
            
            // Tracking
            $table->unsignedBigInteger('created_by');
            $table->decimal('realisasi_anggaran', 15, 2)->default(0);
            $table->decimal('sisa_anggaran', 15, 2)->default(0);
            $table->boolean('is_used')->default(false);
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('redaksi_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('keuangan_approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes dengan nama pendek
            $table->index(['status', 'redaksi_approval_status'], 'pa_status_idx');
            $table->index(['created_by', 'status'], 'pa_user_status_idx');
            $table->index('tanggal_pengajuan', 'pa_tanggal_idx');
            $table->index('keuangan_approval_status', 'pa_keuangan_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_anggarans');
    }
};