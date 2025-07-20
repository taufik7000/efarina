<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Compensation extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar (jamak)
    protected $table = 'compensations';

    protected $fillable = [
        'user_id',
        'work_date',
        'compensation_date',
        'status',
        'work_start_time',
        'work_end_time', 
        'work_hours',
        'work_reason',
        'notes',
        'expires_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'compensation_date' => 'date',
        'expires_at' => 'date',
        'approved_at' => 'datetime',
        'work_hours' => 'decimal:2',
    ];

    // Relasi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function kehadiran(): HasOne
    {
        return $this->hasOne(Kehadiran::class);
    }

    // Accessors
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'earned' => 'Tersedia',
            'used' => 'Sudah Digunakan',
            'expired' => 'Kadaluarsa',
            default => ucfirst($this->status)
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'earned' => 'success',
            'used' => 'info',
            'expired' => 'danger',
            default => 'gray'
        };
    }

    public function getLeaveTypeNameAttribute(): string
    {
        return 'Kompensasi Libur';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() && $this->status === 'earned';
    }

    public function canBeUsed(): bool
    {
        return $this->status === 'earned' && !$this->isExpired();
    }

    // Methods
public function use(Carbon $compensationDate, ?string $notes = null): bool
{
    if (!$this->canBeUsed()) {
        return false;
    }

    // Update status compensation
    $this->update([
        'status' => 'used',
        'compensation_date' => $compensationDate,
        'notes' => $notes,
    ]);

    // Create kehadiran record untuk hari kompensasi
    Kehadiran::create([
        'user_id' => $this->user_id,
        'tanggal' => $compensationDate,
        'status' => 'Kompensasi Libur',
        'compensation_id' => $this->id,  // 🔥 LINK KE COMPENSATION
        'notes' => $notes,
        'metode_absen' => 'system_generated', // 🔥 PERBAIKI NAMA METODE
    ]);

    return true;
}

    public function markExpired(): bool
    {
        if ($this->status !== 'earned') {
            return false;
        }

        return $this->update(['status' => 'expired']);
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('status', 'earned');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'earned')
                    ->where('expires_at', '>', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('work_date', $year)
                    ->whereMonth('work_date', $month);
    }
}