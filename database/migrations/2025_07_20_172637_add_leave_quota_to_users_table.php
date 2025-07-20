<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('monthly_leave_quota')->default(2)->comment('Kuota cuti per bulan (hari)');
            $table->date('employment_start_date')->nullable()->comment('Tanggal mulai kerja');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_leave_quota', 'employment_start_date']);
        });
    }
};