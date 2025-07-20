<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date', 
        'total_days',
        'leave_type',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
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

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }

    // Accessors & Mutators
    public function getLeaveTypeNameAttribute(): string
    {
        return match($this->leave_type) {
            'annual' => 'Cuti Tahunan',
            'sick' => 'Sakit',
            'emergency' => 'Darurat',
            'maternity' => 'Melahirkan',
            'paternity' => 'Ayah Baru',
            'unpaid' => 'Cuti Tanpa Gaji',
            'other' => 'Lainnya',
            default => ucfirst($this->leave_type)
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray'
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status)
        };
    }

    // Helper Methods
    public function calculateWorkingDays(): int
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $workingDays = 0;

        while ($start->lte($end)) {
            // Hitung hanya hari kerja (Senin-Sabtu), kecuali Minggu
            if ($start->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    public function approve($approverId, $notes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        // Update status
        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Create kehadiran records untuk setiap hari cuti
        $this->createAttendanceRecords();

        return true;
    }

    public function reject($approverId, $reason): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    private function createAttendanceRecords(): void
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        while ($start->lte($end)) {
            // Skip Minggu
            if ($start->dayOfWeek !== Carbon::SUNDAY) {
                // Cek apakah sudah ada record kehadiran untuk hari itu
                $existingAttendance = Kehadiran::where('user_id', $this->user_id)
                    ->whereDate('tanggal', $start->toDateString())
                    ->first();

                if (!$existingAttendance) {
                    Kehadiran::create([
                        'user_id' => $this->user_id,
                        'tanggal' => $start->toDateString(),
                        'status' => 'Cuti',
                        'leave_request_id' => $this->id,
                        'notes' => "Cuti {$this->leave_type_name}: {$this->reason}",
                        'metode_absen' => 'system_generated',
                    ]);
                }
            }
            $start->addDay();
        }
    }

    // Scope untuk filtering
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }
}
