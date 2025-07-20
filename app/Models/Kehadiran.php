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
        'compensation_id',  // 🔥 FIELD UNTUK RELASI KE COMPENSATION
        'leave_request_id', // 🔥 FIELD UNTUK RELASI KE LEAVE REQUEST
        'notes',            // 🔥 FIELD UNTUK CATATAN TAMBAHAN
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Mendefinisikan relasi "milik" ke model User.
     * Setiap data kehadiran pasti dimiliki oleh satu pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias untuk relasi user (backward compatibility)
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke LeaveRequest
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    /**
     * 🔥 RELASI KE COMPENSATION
     */
    public function compensation(): BelongsTo
    {
        return $this->belongsTo(Compensation::class, 'compensation_id');
    }

    // Accessors & Helper Methods
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Tepat Waktu' => 'success',
            'Terlambat' => 'warning', 
            'Alfa' => 'danger',
            'Cuti' => 'info',
            'Sakit' => 'warning',
            'Izin' => 'gray',
            'Kompensasi Libur' => 'primary',  // 🔥 STATUS KOMPENSASI
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

    /**
     * Get detail info berdasarkan jenis kehadiran
     */
    public function getDetailInfoAttribute(): ?string
    {
        if ($this->isCompensation() && $this->compensation) {
            return "Kompensasi dari kerja {$this->compensation->work_date->format('d M Y')}: {$this->compensation->work_reason}";
        }
        
        if ($this->status === 'Cuti' && $this->leaveRequest) {
            return "{$this->leaveRequest->leave_type_name}: {$this->leaveRequest->reason}";
        }
        
        return $this->notes;
    }

    /**
     * Scope untuk filtering berdasarkan status
     */
    public function scopePresent($query)
    {
        return $query->whereIn('status', ['Tepat Waktu', 'Terlambat']);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'Alfa');
    }

    public function scopeOnLeave($query)
    {
        return $query->whereIn('status', ['Cuti', 'Sakit', 'Izin', 'Kompensasi Libur']);
    }

    public function scopeCompensation($query)
    {
        return $query->where('status', 'Kompensasi Libur');
    }

    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}