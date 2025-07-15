<?php
// app/Models/TransaksiItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_id',
        'nama_item',
        'deskripsi_item',
        'kuantitas',
        'harga_satuan',
        'subtotal',
        'satuan',
    ];

    protected $casts = [
        'kuantitas' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relasi
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Mutator untuk auto-calculate subtotal
    public function setKuantitasAttribute($value)
    {
        $this->attributes['kuantitas'] = $value;
        $this->calculateSubtotal();
    }

    public function setHargaSatuanAttribute($value)
    {
        $this->attributes['harga_satuan'] = $value;
        $this->calculateSubtotal();
    }

    private function calculateSubtotal()
    {
        if (isset($this->attributes['kuantitas']) && isset($this->attributes['harga_satuan'])) {
            $this->attributes['subtotal'] = $this->attributes['kuantitas'] * $this->attributes['harga_satuan'];
        }
    }

    // Boot method untuk update total di transaksi
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->transaksi->updateTotalFromItems();
        });

        static::deleted(function ($item) {
            $item->transaksi->updateTotalFromItems();
        });
    }
}