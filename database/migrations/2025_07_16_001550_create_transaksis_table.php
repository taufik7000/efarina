<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_transaksis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->enum('jenis_transaksi', ['pemasukan', 'pengeluaran']);
            $table->date('tanggal_transaksi');
            $table->string('nama_transaksi');
            $table->text('deskripsi')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'completed'])->default('draft');
            $table->string('metode_pembayaran')->nullable(); // cash, transfer, dll
            $table->string('nomor_referensi')->nullable(); // no invoice, no kwitansi
            $table->foreignId('budget_allocation_id')->nullable()->constrained('budget_allocations');
            $table->foreignId('project_id')->nullable()->constrained('projects'); // jika terkait project
            $table->json('attachments')->nullable(); // file pendukung
            $table->text('catatan_approval')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['tanggal_transaksi', 'jenis_transaksi']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};