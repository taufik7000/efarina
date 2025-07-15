<?php
// app/Models/BudgetCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'deskripsi',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(BudgetSubcategory::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    // Scope untuk kategori aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Method untuk mendapatkan total alokasi kategori ini
    public function getTotalAllocatedAttribute(): float
    {
        return $this->allocations()->sum('allocated_amount');
    }

    // Method untuk mendapatkan total penggunaan kategori ini
    public function getTotalUsedAttribute(): float
    {
        return $this->allocations()->sum('used_amount');
    }
}