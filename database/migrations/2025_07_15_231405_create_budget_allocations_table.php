<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_budget_allocations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('budget_category_id')->constrained();
            $table->foreignId('budget_subcategory_id')->nullable()->constrained();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->storedAs('allocated_amount - used_amount');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Index untuk performa
            $table->index(['budget_plan_id', 'budget_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};