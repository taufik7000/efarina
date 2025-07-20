<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date')->comment('Tanggal masuk kerja di hari libur');
            $table->date('compensation_date')->nullable()->comment('Tanggal kompensasi yang diambil');
            $table->enum('status', ['earned', 'used', 'expired'])->default('earned');
            $table->time('work_start_time')->nullable()->comment('Jam mulai kerja');
            $table->time('work_end_time')->nullable()->comment('Jam selesai kerja');
            $table->decimal('work_hours', 5, 2)->nullable()->comment('Total jam kerja');
            $table->text('work_reason')->comment('Alasan kerja di hari libur');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->date('expires_at')->comment('Tanggal kadaluarsa kompensasi');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['work_date', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensations');
    }
};