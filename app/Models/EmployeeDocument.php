<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'description',
        'uploaded_at',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Constants untuk document types
    public const DOCUMENT_TYPES = [
        'ktp' => 'KTP',
        'cv' => 'CV/Resume',
        'kontrak' => 'Kontrak Kerja',
        'ijazah' => 'Ijazah',
        'sertifikat' => 'Sertifikat',
        'foto' => 'Foto Profil',
        'npwp' => 'NPWP',
        'bpjs' => 'BPJS',
        'other' => 'Lainnya'
    ];

    // ===== RELASI =====
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ===== ACCESSORS =====
    public function getDocumentTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }
        
        $bytes = (int) $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_verified) {
            return [
                'label' => 'Terverifikasi',
                'color' => 'success',
                'icon' => 'heroicon-o-check-circle'
            ];
        }
        
        return [
            'label' => 'Menunggu Verifikasi',
            'color' => 'warning', 
            'icon' => 'heroicon-o-clock'
        ];
    }

    public function getFileTypeIconAttribute(): string
    {
        if (!$this->file_name) {
            return 'heroicon-o-document';
        }

        $extension = pathinfo($this->file_name, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'pdf' => 'heroicon-o-document-text',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'heroicon-o-photo',
            'doc', 'docx' => 'heroicon-o-document',
            'xls', 'xlsx' => 'heroicon-o-table-cells',
            'ppt', 'pptx' => 'heroicon-o-presentation-chart-bar',
            'zip', 'rar' => 'heroicon-o-archive-box',
            default => 'heroicon-o-document'
        };
    }

    public function getUploadedTimeAgoAttribute(): string
    {
        $uploadedAt = $this->uploaded_at ?? $this->created_at;
        return $uploadedAt->diffForHumans();
    }

    // ===== HELPER METHODS =====
    public function verify(User $verifier, ?string $notes = null): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
            'verification_notes' => $notes ?? 'Diverifikasi oleh HRD',
        ]);
    }

    public function unverify(): bool
    {
        return $this->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'verification_notes' => null,
        ]);
    }

    public function fileExists(): bool
    {
        return $this->file_path && Storage::exists($this->file_path);
    }

    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }
        
        return true;
    }

    public function isImage(): bool
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->mime_type, $imageTypes);
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isOfficeDocument(): bool
    {
        $officeTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        
        return in_array($this->mime_type, $officeTypes);
    }

    // ===== SCOPES =====
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ===== BOOT METHODS =====
    protected static function boot()
    {
        parent::boot();

        // Auto-set uploaded_at jika belum diset
        static::creating(function ($document) {
            if (!$document->uploaded_at) {
                $document->uploaded_at = now();
            }
        });

        // Hapus file saat model dihapus
        static::deleting(function ($document) {
            $document->deleteFile();
        });
    }

    // ===== STATIC METHODS =====
    public static function getDocumentTypeOptions(): array
    {
        return self::DOCUMENT_TYPES;
    }

    public static function getUserDocumentsSummary($userId): array
    {
        $documents = self::where('user_id', $userId)->get();
        
        return [
            'total' => $documents->count(),
            'verified' => $documents->where('is_verified', true)->count(),
            'pending' => $documents->where('is_verified', false)->count(),
            'by_type' => $documents->groupBy('document_type')->map->count(),
        ];
    }
}