<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'compensation_id',  // 🔥 FIELD BARU DARI MIGRATION
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

    /**
     * 🔥 RELASI KE COMPENSATION
     */
    public function compensation(): BelongsTo
    {
        return $this->belongsTo(Compensation::class);
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
            'Kompensasi Libur' => 'primary',  // 🔥 TAMBAHAN STATUS BARU
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
        return in_array($this->status, ['Cuti', 'Sakit', 'Izin', 'Kompensasi Libur']); // 🔥 TAMBAHAN
    }

    /**
     * Check if this attendance is compensation
     */
    public function isCompensation(): bool
    {
        return $this->status === 'Kompensasi Libur';
    }

    /**
     * Check if this attendance is holiday work
     */
    public function isHolidayWork(): bool
    {
        return $this->tanggal && Carbon::parse($this->tanggal)->dayOfWeek === Carbon::SUNDAY;
    }

    /**
     * Get formatted status dengan emoji
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            'Tepat Waktu' => '✅ Tepat Waktu',
            'Terlambat' => '⏰ Terlambat',
            'Alfa' => '❌ Alfa',
            'Cuti' => '🏖️ Cuti',
            'Sakit' => '🤒 Sakit',
            'Izin' => '📝 Izin',
            'Kompensasi Libur' => '🔄 Kompensasi Libur',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }
}