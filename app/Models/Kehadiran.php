<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'kehadiran';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'metode_absen',
        'status',
        'lokasi_masuk',
        'lokasi_pulang',
        'info_perangkat_masuk',
        'info_perangkat_pulang',
    ];

    /**
     * Mendefinisikan relasi "milik" ke model User.
     * Setiap data kehadiran pasti dimiliki oleh satu pengguna.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveRequest(): BelongsTo
{
    return $this->belongsTo(LeaveRequest::class);
}

public function getStatusColorAttribute(): string
{
    return match($this->status) {
        'Tepat Waktu' => 'success',
        'Terlambat' => 'warning', 
        'Alfa' => 'danger',
        'Cuti' => 'info',
        'Sakit' => 'warning',
        'Izin' => 'gray',
        default => 'gray'
    };
}

public function isPresent(): bool
{
    return in_array($this->status, ['Tepat Waktu', 'Terlambat']);
}

public function isAbsent(): bool
{
    return $this->status === 'Alfa';
}

public function isOnLeave(): bool
{
    return in_array($this->status, ['Cuti', 'Sakit', 'Izin']);
}
}