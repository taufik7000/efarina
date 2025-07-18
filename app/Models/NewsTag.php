<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class NewsTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_tag',
        'slug',
        'color',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'color' => '#10b981',
    ];

    // Relations
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_news_tag');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount(['news' => function ($query) {
            $query->where('status', 'published');
        }])->orderBy('news_count', 'desc')->limit($limit);
    }

    // Accessors & Mutators
    public function setNamaTagAttribute($value)
    {
        $this->attributes['nama_tag'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getNewsCountAttribute(): int
    {
        return $this->news()->where('status', 'published')->count();
    }

    public function getColorStyleAttribute(): string
    {
        return "background-color: {$this->color}; color: white;";
    }

    // Helper Methods
    public function canBeDeleted(): bool
    {
        return $this->news()->count() === 0;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nama_tag;
    }

    // Static Methods
    public static function findOrCreateByName(string $name, int $createdBy): self
    {
        $slug = Str::slug($name);
        
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'nama_tag' => $name,
                'created_by' => $createdBy,
            ]
        );
    }
}