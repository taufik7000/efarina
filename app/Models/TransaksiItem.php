<?php

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

    // Boot method untuk auto-calculate subtotal dan update total transaksi
    protected static function boot()
    {
        parent::boot();

        // Before saving: calculate subtotal
        static::saving(function ($item) {
            $item->subtotal = ($item->kuantitas ?? 0) * ($item->harga_satuan ?? 0);
        });

        // After saved: update total di transaksi
        static::saved(function ($item) {
            if ($item->transaksi) {
                $item->transaksi->updateTotalFromItems();
            }
        });

        // After deleted: update total di transaksi
        static::deleted(function ($item) {
            if ($item->transaksi) {
                $item->transaksi->updateTotalFromItems();
            }
        });
    }

    // Accessor untuk format currency
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedHargaSatuanAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }
}