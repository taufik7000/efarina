<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum untuk status project
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('draft', 'planning', 'in_progress', 'review', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
        
        // Update data yang ada: 'active' -> 'in_progress' 
        DB::table('projects')
            ->where('status', 'active')
            ->update(['status' => 'in_progress']);
    }

    public function down(): void
    {
        // Kembalikan ke enum lama
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('planning', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'planning'");
        
        // Update data kembali: 'in_progress' -> 'active', 'draft' -> 'planning'
        DB::table('projects')
            ->where('status', 'in_progress')
            ->update(['status' => 'active']);
            
        DB::table('projects')
            ->where('status', 'draft')
            ->update(['status' => 'planning']);
    }
};