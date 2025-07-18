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
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->id();
            $table->string('video_id')->unique()->comment('YouTube Video ID');
            $table->string('channel_id')->comment('YouTube Channel ID');
            $table->string('channel_title');
            $table->unsignedBigInteger('video_category_id')->nullable();
            
            // Video Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamp('published_at');
            $table->string('duration_iso')->nullable()->comment('ISO 8601 format PT4M13S');
            $table->integer('duration_seconds')->nullable();
            $table->bigInteger('view_count')->default(0);
            $table->bigInteger('like_count')->default(0);
            $table->json('tags')->nullable();
            
            // Website Control
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('custom_description')->nullable()->comment('Deskripsi custom untuk website');
            
            // Sync Tracking
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('video_category_id')
                  ->references('id')
                  ->on('video_categories')
                  ->onDelete('set null');

            // Indexes untuk Performance
            $table->index(['is_active', 'published_at']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['channel_id', 'published_at']);
            $table->index(['video_category_id', 'published_at']);
            $table->index('view_count');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_videos');
    }
};