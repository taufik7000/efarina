<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_budget_subcategories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_category_id')->constrained()->onDelete('cascade');
            $table->string('nama_subkategori');
            $table->string('kode_subkategori')->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_subcategories');
    }
};