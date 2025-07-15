<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaksi_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained()->onDelete('cascade');
            $table->string('status_from')->nullable(); // null untuk creation
            $table->string('status_to');
            $table->foreignId('action_by')->constrained('users');
            $table->timestamp('action_at');
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['transaksi_id', 'action_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaksi_histories');
    }
};