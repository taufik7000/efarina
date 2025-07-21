<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'kontak_darurat_nama',
        'kontak_darurat_telp',
        'gaji_pokok',
        'no_rekening',
        'npwp',
        'notes_hrd',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'gaji_pokok' => 'decimal:2',
    ];

    // Relasi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors & Helper Methods
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getBirthPlaceFullAttribute(): ?string
    {
        if (!$this->tempat_lahir || !$this->tanggal_lahir) {
            return null;
        }
        
        return $this->tempat_lahir . ', ' . $this->tanggal_lahir->format('d M Y');
    }

    public function getFormattedGajiAttribute(): string
    {
        if (!$this->gaji_pokok) {
            return 'Belum diatur';
        }
        
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getMaskedNpwpAttribute(): ?string
    {
        if (!$this->npwp) {
            return null;
        }
        
        // Format NPWP: XX.XXX.XXX.X-XXX.XXX
        $npwp = preg_replace('/[^0-9]/', '', $this->npwp);
        
        if (strlen($npwp) === 15) {
            return substr($npwp, 0, 2) . '.***.***.'.substr($npwp, 8, 1).'-***.***';
        }
        
        return $this->npwp;
    }

    public function getMaskedRekeningAttribute(): ?string
    {
        if (!$this->no_rekening) {
            return null;
        }
        
        $length = strlen($this->no_rekening);
        if ($length <= 4) {
            return $this->no_rekening;
        }
        
        return substr($this->no_rekening, 0, 3) . str_repeat('*', $length - 6) . substr($this->no_rekening, -3);
    }

    // Helper Methods
    public function isProfileComplete(): bool
    {
        $requiredFields = [
            'nik_ktp',
            'tempat_lahir', 
            'tanggal_lahir',
            'alamat',
            'kontak_darurat_nama',
            'kontak_darurat_telp'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        
        return true;
    }

    public function getProfileCompletionPercentage(): int
    {
        $allFields = [
            'nik_ktp',
            'tempat_lahir',
            'tanggal_lahir', 
            'alamat',
            'kontak_darurat_nama',
            'kontak_darurat_telp',
            'gaji_pokok',
            'no_rekening',
            'npwp'
        ];
        
        $filledFields = 0;
        foreach ($allFields as $field) {
            if (!empty($this->$field)) {
                $filledFields++;
            }
        }
        
        return round(($filledFields / count($allFields)) * 100);
    }

    // Scopes
    public function scopeComplete($query)
    {
        return $query->whereNotNull('nik_ktp')
                    ->whereNotNull('tempat_lahir')
                    ->whereNotNull('tanggal_lahir')
                    ->whereNotNull('alamat')
                    ->whereNotNull('kontak_darurat_nama')
                    ->whereNotNull('kontak_darurat_telp');
    }

    public function scopeIncomplete($query)
    {
        return $query->where(function($q) {
            $q->whereNull('nik_ktp')
              ->orWhereNull('tempat_lahir')
              ->orWhereNull('tanggal_lahir')
              ->orWhereNull('alamat')
              ->orWhereNull('kontak_darurat_nama')
              ->orWhereNull('kontak_darurat_telp');
        });
    }

    public function scopeWithSalary($query)
    {
        return $query->whereNotNull('gaji_pokok');
    }

    public function scopeBornInYear($query, $year)
    {
        return $query->whereYear('tanggal_lahir', $year);
    }
}