<?php
// app/Models/BudgetSubcategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_category_id',
        'nama_subkategori',
        'kode_subkategori',
        'deskripsi',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    // Scope untuk subkategori aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Method untuk mendapatkan nama lengkap (kategori - subkategori)
    public function getFullNameAttribute(): string
    {
        return $this->category->nama_kategori . ' - ' . $this->nama_subkategori;
    }
}