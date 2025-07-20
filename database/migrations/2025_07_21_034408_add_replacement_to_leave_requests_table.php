<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // ID user yang akan menggantikan, nullable karena tidak semua cuti butuh pengganti
            $table->foreignId('replacement_user_id')->nullable()->after('user_id')->constrained('users');

            // Status persetujuan dari pengganti (pending, approved, rejected)
            $table->string('replacement_status')->default('pending')->after('replacement_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['replacement_user_id']);
            $table->dropColumn(['replacement_user_id', 'replacement_status']);
        });
    }
};