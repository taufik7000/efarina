<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanAnggaranItem extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'pengajuan_anggaran_items';

    /**
     * Menonaktifkan timestamp (created_at dan updated_at) jika tidak dibutuhkan.
     * Anda bisa mengaturnya ke 'true' jika ingin melacak kapan item dibuat/diubah.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Kolom yang diizinkan untuk diisi secara massal (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pengajuan_anggaran_id',
        'nama_item',
        'kuantitas',
        'harga_satuan',
        'catatan',
    ];

    /**
     * Casting tipe data untuk atribut.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'kuantitas' => 'integer',
        'harga_satuan' => 'decimal:2',
    ];

    /**
     * Mendefinisikan relasi "milik" (belongs-to) ke model PengajuanAnggaran.
     * Setiap item pasti milik satu pengajuan anggaran.
     */
    public function pengajuanAnggaran(): BelongsTo
    {
        return $this->belongsTo(PengajuanAnggaran::class, 'pengajuan_anggaran_id');
    }
}