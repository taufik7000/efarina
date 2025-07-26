<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_histories', function (Blueprint $table) {
            // Tambahkan kolom user_id setelah transaksi_id
            $table->foreignId('user_id')->nullable()->after('transaksi_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_histories', function (Blueprint $table) {
            // Hapus kolom jika migration di-rollback
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};