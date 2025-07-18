<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubeVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'channel_id', 
        'channel_title',
        'video_category_id',
        'title',
        'description',
        'thumbnail_url',
        'published_at',
        'duration_iso',
        'duration_seconds',
        'view_count',
        'like_count',
        'tags',
        'is_active',
        'is_featured',
        'sort_order',
        'custom_description',
        'last_sync_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'tags' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'duration_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
        'view_count' => 0,
        'like_count' => 0,
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(VideoCategory::class, 'video_category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeByChannel($query, string $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('video_category_id', $categoryId);
    }

    public function scopeUncategorized($query)
    {
        return $query->whereNull('video_category_id');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    // Accessors - FIXED untuk thumbnail
    public function getWatchUrlAttribute(): string
    {
        return "https://www.youtube.com/watch?v={$this->video_id}";
    }

    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube.com/embed/{$this->video_id}";
    }

    // FIX: Thumbnail getter yang benar
    public function getThumbnailHqAttribute(): string
    {
        // Jika ada thumbnail_url dari database, gunakan itu
        if (!empty($this->thumbnail_url)) {
            return $this->thumbnail_url;
        }
        
        // Fallback ke thumbnail YouTube default
        return "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg";
    }

    public function getThumbnailMaxresAttribute(): string
    {
        // Jika ada thumbnail_url dari database, gunakan itu
        if (!empty($this->thumbnail_url)) {
            return $this->thumbnail_url;
        }
        
        // Fallback ke thumbnail YouTube maxres
        return "https://img.youtube.com/vi/{$this->video_id}/maxresdefault.jpg";
    }

    // FIX: Getter untuk thumbnail yang digunakan di Filament
    public function getThumbnailDisplayAttribute(): string
    {
        return $this->getThumbnailHqAttribute();
    }

    public function getChannelUrlAttribute(): string
    {
        return "https://www.youtube.com/channel/{$this->channel_id}";
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) return '00:00';

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedViewCountAttribute(): string
    {
        if ($this->view_count >= 1000000) {
            return number_format($this->view_count / 1000000, 1) . 'M';
        } elseif ($this->view_count >= 1000) {
            return number_format($this->view_count / 1000, 1) . 'K';
        }

        return number_format($this->view_count);
    }

    public function getAgeAttribute(): string
    {
        return $this->published_at->diffForHumans();
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->custom_description ?: $this->description;
    }

    public function getSlugAttribute(): string
    {
        return $this->video_id; // Menggunakan video_id sebagai slug
    }

    // FIX: Getter untuk title (pastikan tidak ada masalah)
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: 'Untitled Video';
    }

    // Helper Methods
    public static function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/^([a-zA-Z0-9_-]{11})$/' // Direct video ID
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function updateFromApiData(array $data): bool
    {
        return $this->update([
            'title' => $data['title'] ?? $this->title,
            'description' => $data['description'] ?? $this->description,
            'thumbnail_url' => $data['thumbnail_url'] ?? $this->thumbnail_url,
            'duration_iso' => $data['duration_iso'] ?? $this->duration_iso,
            'duration_seconds' => $data['duration_seconds'] ?? $this->duration_seconds,
            'view_count' => $data['view_count'] ?? $this->view_count,
            'like_count' => $data['like_count'] ?? $this->like_count,
            'tags' => $data['tags'] ?? $this->tags,
            'channel_title' => $data['channel_title'] ?? $this->channel_title,
            'last_sync_at' => now(),
        ]);
    }

    public function shouldSync(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Sync jika belum pernah sync atau sudah lebih dari 24 jam
        return is_null($this->last_sync_at) || 
               $this->last_sync_at->lt(now()->subHours(24));
    }

    public function convertDurationToSeconds(): void
    {
        if ($this->duration_iso && !$this->duration_seconds) {
            $this->duration_seconds = $this->parseDurationFromIso($this->duration_iso);
            $this->save();
        }
    }

    private function parseDurationFromIso(string $duration): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
        
        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    // Static Methods
    public static function findByVideoId(string $videoId): ?self
    {
        return static::where('video_id', $videoId)->first();
    }

    public static function createFromApiData(array $data): self
    {
        $video = static::create($data);
        
        // Convert duration ISO to seconds if available
        if ($video->duration_iso) {
            $video->convertDurationToSeconds();
        }
        
        return $video;
    }
}