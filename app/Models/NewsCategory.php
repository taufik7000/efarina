<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'color',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'color' => '#6366f1',
    ];

    // Relations
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'news_category_id');
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

    // Accessors & Mutators
    public function setNamaKategoriAttribute($value)
    {
        $this->attributes['nama_kategori'] = $value;
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
        return $this->nama_kategori;
    }
}