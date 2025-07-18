<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VideoCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'color' => '#3b82f6',
    ];

    // Relations
    public function videos(): HasMany
    {
        return $this->hasMany(YoutubeVideo::class);
    }

    public function activeVideos(): HasMany
    {
        return $this->hasMany(YoutubeVideo::class)->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('nama_kategori');
    }

    public function scopeWithVideoCount($query)
    {
        return $query->withCount(['activeVideos']);
    }

    // Mutators
    public function setNamaKategoriAttribute($value)
    {
        $this->attributes['nama_kategori'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Accessors
    public function getVideoCountAttribute(): int
    {
        return $this->activeVideos()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nama_kategori;
    }

    public function getIconClassAttribute(): string
    {
        return $this->icon ?: 'fas fa-video';
    }

    // Helper Methods
    public function canBeDeleted(): bool
    {
        return $this->videos()->count() === 0;
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}