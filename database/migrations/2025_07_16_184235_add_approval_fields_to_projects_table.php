<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('redaksi_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('keuangan_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('redaksi_approved_by')->nullable();
            $table->unsignedBigInteger('keuangan_approved_by')->nullable();
            $table->timestamp('redaksi_approved_at')->nullable();
            $table->timestamp('keuangan_approved_at')->nullable();
            $table->text('redaksi_notes')->nullable();
            $table->text('keuangan_notes')->nullable();
            
            $table->foreign('redaksi_approved_by')->references('id')->on('users');
            $table->foreign('keuangan_approved_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['redaksi_approved_by']);
            $table->dropForeign(['keuangan_approved_by']);
            $table->dropColumn([
                'redaksi_approval_status',
                'keuangan_approval_status',
                'redaksi_approved_by',
                'keuangan_approved_by',
                'redaksi_approved_at',
                'keuangan_approved_at',
                'redaksi_notes',
                'keuangan_notes'
            ]);
        });
    }
};