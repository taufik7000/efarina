<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_id',
        'user_id',
        'status_from',
        'status_to',
        'action_by',
        'action_at',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    

    // Accessor untuk format status yang readable
    public function getFormattedStatusFromAttribute(): string
    {
        return match($this->status_from) {
            'draft' => 'Draft',
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            default => $this->status_from ?? 'Baru dibuat',
        };
    }

    public function getFormattedStatusToAttribute(): string
    {
        return match($this->status_to) {
            'draft' => 'Draft',
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            default => $this->status_to,
        };
    }

    public function getActionDescriptionAttribute(): string
    {
        if (!$this->status_from) {
            return "Transaksi dibuat dengan status {$this->formatted_status_to}";
        }
        
        return "Status diubah dari {$this->formatted_status_from} ke {$this->formatted_status_to}";
    }

    
}