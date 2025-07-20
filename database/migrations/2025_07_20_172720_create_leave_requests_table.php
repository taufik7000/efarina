<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('start_date')->comment('Tanggal mulai cuti');
            $table->date('end_date')->comment('Tanggal selesai cuti');
            $table->integer('total_days')->comment('Total hari cuti');
            $table->enum('leave_type', [
                'annual', 'sick', 'emergency', 'maternity', 'paternity', 'unpaid', 'other'
            ])->default('annual');
            $table->text('reason')->comment('Alasan cuti');
            $table->string('attachment')->nullable()->comment('Lampiran (surat dokter, dll)');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->text('approval_notes')->nullable()->comment('Catatan dari approver');
            $table->text('rejection_reason')->nullable()->comment('Alasan penolakan');
            $table->timestamps();

            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};