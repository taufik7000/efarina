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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->unsignedBigInteger('pengajuan_anggaran_id')->nullable()->after('project_id');
            $table->foreign('pengajuan_anggaran_id')->references('id')->on('pengajuan_anggarans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropForeign(['pengajuan_anggaran_id']);
            $table->dropColumn('pengajuan_anggaran_id');
        });
    }
};