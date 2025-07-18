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
        Schema::table('news', function (Blueprint $table) {
            // SEO Fields
            $table->string('seo_title')->nullable()->after('meta_data');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('focus_keyword')->nullable()->after('seo_description');
            $table->json('seo_keywords')->nullable()->after('focus_keyword');
            $table->string('canonical_url')->nullable()->after('seo_keywords');
            
            // Open Graph Fields
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            
            // Twitter Card Fields
            $table->enum('twitter_card_type', ['summary', 'summary_large_image'])->default('summary_large_image')->after('og_image');
            $table->string('twitter_title')->nullable()->after('twitter_card_type');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image')->nullable()->after('twitter_description');
            
            // SEO Score (calculated field)
            $table->integer('seo_score')->default(0)->after('twitter_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title',
                'seo_description', 
                'focus_keyword',
                'seo_keywords',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image',
                'twitter_card_type',
                'twitter_title',
                'twitter_description', 
                'twitter_image',
                'seo_score',
            ]);
        });
    }
};