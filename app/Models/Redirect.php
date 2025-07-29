<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_url',
        'new_url',
        'status_code',
        'is_active',
        'hit_count',
        'last_accessed_at',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function incrementHitCount(): void
    {
        $this->increment('hit_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function getFormattedOldUrlAttribute(): string
    {
        return ltrim($this->old_url, '/');
    }

    public function getFormattedNewUrlAttribute(): string
    {
        // Jika URL baru adalah URL internal, tambahkan domain
        if (!str_starts_with($this->new_url, 'http')) {
            return url($this->new_url);
        }
        return $this->new_url;
    }
}