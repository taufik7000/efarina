<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            // Add playlist_id column after video_category_id
            $table->string('playlist_id', 50)->nullable()->after('video_category_id');
            
            // Add index for better performance
            $table->index('playlist_id');
            
            // Optional: Add foreign key constraint if you have youtube_playlists table
            // $table->foreign('playlist_id')->references('playlist_id')->on('youtube_playlists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            // Drop foreign key first if exists
            // $table->dropForeign(['playlist_id']);
            
            // Drop index
            $table->dropIndex(['playlist_id']);
            
            // Drop column
            $table->dropColumn('playlist_id');
        });
    }
};