<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_budget_plans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_period_id')->constrained()->onDelete('cascade');
            $table->string('nama_budget');
            $table->decimal('total_budget', 15, 2);
            $table->decimal('total_allocated', 15, 2)->default(0);
            $table->decimal('total_used', 15, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'active', 'closed'])->default('draft');
            $table->text('deskripsi')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_plans');
    }
};