<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Update ENUM untuk status project
            $table->enum('status', [
                'planning', 
                'active', 
                'completed', 
                'cancelled'
            ])->default('planning')->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Rollback ke ENUM lama jika perlu
            $table->enum('status', [
                'draft', 
                'active', 
                'completed', 
                'cancelled'
            ])->default('draft')->change();
        });
    }
};