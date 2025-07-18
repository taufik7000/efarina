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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('excerpt'); // Ringkasan berita
            $table->longText('konten'); // Konten lengkap
            $table->string('thumbnail')->nullable(); // Path gambar utama
            $table->json('gallery')->nullable(); // Multiple images
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false); // Berita unggulan
            $table->integer('views_count')->default(0); // Jumlah pembaca
            $table->timestamp('published_at')->nullable(); // Tanggal publikasi
            $table->foreignId('news_category_id')->constrained('news_categories');
            $table->foreignId('author_id')->constrained('users'); // Penulis
            $table->foreignId('editor_id')->nullable()->constrained('users'); // Editor
            $table->timestamp('edited_at')->nullable(); // Tanggal edit terakhir
            $table->json('meta_data')->nullable(); // SEO meta, social sharing, etc
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['status', 'published_at']);
            $table->index(['news_category_id', 'status']);
            $table->index(['is_featured', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};