<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_transaksi_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->onDelete('cascade');
            $table->string('nama_item');
            $table->text('deskripsi_item')->nullable();
            $table->integer('kuantitas')->default(1);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2); // kuantitas * harga_satuan
            $table->string('satuan')->nullable(); // pcs, kg, buah, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_items');
    }
};