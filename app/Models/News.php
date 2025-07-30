<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'slug',
        'excerpt',
        'konten',
        'thumbnail',
        'gallery',
        'status',
        'is_featured',
        'views_count',
        'published_at',
        'news_category_id',
        'author_id',
        'editor_id',
        'edited_at',
        'meta_data',
        // SEO Fields
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
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'edited_at' => 'datetime',
        'meta_data' => 'array',
        'seo_keywords' => 'array',
        'seo_score' => 'integer',
    ];

    protected $attributes = [
        'status' => 'draft',
        'is_featured' => false,
        'views_count' => 0,
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_news_tag');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('news_category_id', $categoryId);
    }

    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('news_tags.id', $tagId);
        });
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('published_at', 'desc')
                    ->limit($limit);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('views_count', 'desc')
                    ->limit($limit);
    }

    // Accessors & Mutators
    public function setJudulAttribute($value)
    {
        $this->attributes['judul'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'published' => 'success',
            'archived' => 'warning',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
            default => 'Unknown',
        };
    }

    public function getReadingTimeAttribute(): string
    {
        $wordCount = str_word_count(strip_tags($this->konten));
        $minutes = ceil($wordCount / 200); // Rata-rata 200 kata per menit
        return $minutes . ' min read';
    }

    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at ? 
            $this->published_at->format('d F Y, H:i') : 
            'Belum dipublikasi';
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? 
            asset('storage/' . $this->thumbnail) : 
            null;
    }

    // Helper Methods
    public function canBePublished(): bool
    {
        return $this->status === 'draft' && 
               !empty($this->judul) && 
               !empty($this->konten) && 
               !empty($this->excerpt);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'published']);
    }

    public function publish(): bool
    {
        if (!$this->canBePublished()) {
            return false;
        }

        return $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function syncTags(array $tagNames): void
    {
        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            if (!empty(trim($tagName))) {
                $tag = NewsTag::findOrCreateByName(trim($tagName), $this->author_id);
                $tagIds[] = $tag->id;
            }
        }
        
        $this->tags()->sync($tagIds);
    }

    // SEO Helper Methods
    public function calculateSeoScore(): int
    {
        $score = 0;
        
        // Title analysis (20 points)
        $title = $this->seo_title ?: $this->judul;
        if (!empty($title)) {
            if (strlen($title) >= 30 && strlen($title) <= 60) {
                $score += 20;
            } elseif (strlen($title) >= 20 && strlen($title) <= 70) {
                $score += 15;
            } else {
                $score += 5;
            }
        }
        
        // Description analysis (20 points)
        $description = $this->seo_description ?: $this->excerpt;
        if (!empty($description)) {
            if (strlen($description) >= 120 && strlen($description) <= 160) {
                $score += 20;
            } elseif (strlen($description) >= 100 && strlen($description) <= 180) {
                $score += 15;
            } else {
                $score += 5;
            }
        }
        
        // Focus keyword analysis (30 points)
        if (!empty($this->focus_keyword)) {
            $score += 10;
            
            // Check in title
            if ($title && stripos($title, $this->focus_keyword) !== false) {
                $score += 10;
            }
            
            // Check in description
            if ($description && stripos($description, $this->focus_keyword) !== false) {
                $score += 10;
            }
        }
        
        // Content analysis (20 points)
        if (!empty($this->konten)) {
            $wordCount = str_word_count(strip_tags($this->konten));
            if ($wordCount >= 300) {
                $score += 20;
            } elseif ($wordCount >= 200) {
                $score += 15;
            } elseif ($wordCount >= 100) {
                $score += 10;
            }
        }
        
        // Social media optimization (10 points)
        if (!empty($this->og_title) && !empty($this->og_description)) {
            $score += 10;
        }
        
        return min(100, $score);
    }

    public function updateSeoScore(): void
    {
        $this->update(['seo_score' => $this->calculateSeoScore()]);
    }

    public function getSeoScoreColorAttribute(): string
    {
        return match (true) {
            $this->seo_score >= 80 => 'success',
            $this->seo_score >= 60 => 'warning', 
            default => 'danger'
        };
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->attributes['seo_title'] ?: $this->judul ?: '';
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->attributes['seo_description'] ?: $this->excerpt ?: '';
    }

    public function getCanonicalUrlAttribute(): string
    {
        return $this->attributes['canonical_url'] ?: url('/berita/' . $this->slug);
    }


    public static function generateSmartSlug(string $title, int $maxLength = 120): string
{
    // Daftar kata-kata yang bisa dihapus untuk mempersingkat
    $stopWords = [
        'yang', 'dan', 'atau', 'dengan', 'untuk', 'dari', 'ke', 'di', 'pada',
        'dalam', 'oleh', 'tentang', 'seperti', 'adalah', 'akan', 'telah',
        'ini', 'itu', 'nya', 'an', 'a', 'the', 'is', 'are', 'was', 'were',
        'in', 'on', 'at', 'by', 'for', 'with', 'to', 'from', 'of', 'and', 'or'
    ];
    
    // Buat slug dasar
    $slug = Str::slug($title);
    
    // Jika sudah cukup pendek, return
    if (strlen($slug) <= $maxLength) {
        return $slug;
    }
    
    // Pecah menjadi kata-kata
    $words = explode('-', $slug);
    
    // Hapus stop words jika masih terlalu panjang
    if (strlen($slug) > $maxLength) {
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords);
        });
        
        if (count($filteredWords) > 2) { // Pastikan masih ada kata yang bermakna
            $words = array_values($filteredWords);
        }
    }
    
    // Gabungkan kata-kata hingga mendekati batas maksimal
    $result = '';
    foreach ($words as $word) {
        $test = $result ? $result . '-' . $word : $word;
        if (strlen($test) <= $maxLength) {
            $result = $test;
        } else {
            break;
        }
    }
    
    // Jika masih kosong atau terlalu pendek, ambil kata-kata penting
    if (strlen($result) < 10 && count($words) > 0) {
        $result = '';
        $importantWords = array_slice($words, 0, 3); // Ambil 3 kata pertama
        
        foreach ($importantWords as $word) {
            $test = $result ? $result . '-' . $word : $word;
            if (strlen($test) <= $maxLength) {
                $result = $test;
            } else {
                // Potong kata jika perlu
                $remaining = $maxLength - strlen($result) - 1;
                if ($remaining > 3) {
                    $result .= '-' . substr($word, 0, $remaining);
                }
                break;
            }
        }
    }
    
    return $result ?: substr($slug, 0, $maxLength);
}

// Update method truncateSlug untuk menggunakan smart generator
public static function truncateSlug(string $text, int $maxLength = 80): string
{
    return self::generateSmartSlug($text, $maxLength);
}
    // Boot method untuk auto-generate slug dan SEO score
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($news) {
        if (empty($news->slug)) {
            $news->slug = self::generateShortSlug($news->judul, 80);
        }
        $news->seo_score = $news->calculateSeoScore();
    });

    static::updating(function ($news) {
        // Update slug jika judul berubah
        if ($news->isDirty('judul')) {
            $news->slug = self::generateShortSlug($news->judul, 80);
        }
        // Update SEO score ketika field SEO berubah
        if ($news->isDirty(['judul', 'excerpt', 'konten', 'seo_title', 'seo_description', 'focus_keyword'])) {
            $news->seo_score = $news->calculateSeoScore();
        }
    });


}




}