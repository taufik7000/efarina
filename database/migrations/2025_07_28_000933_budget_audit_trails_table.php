<?php
// database/migrations/xxxx_create_budget_audit_trails_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // BudgetPlan atau BudgetAllocation
            $table->unsignedBigInteger('auditable_id');
            $table->string('action'); // created, updated, deleted, budget_increased, allocation_added, etc
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->decimal('amount_changed', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('user_name');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_audit_trails');
    }
};