<?php

// app/Models/EmployeeDocument.php
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
        'skck' => 'SKCK',
        'surat_sehat' => 'Surat Keterangan Sehat',
        'referensi' => 'Surat Referensi',
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
        
        return Storage::disk('public')->url($this->file_path);
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

    public function getFileTypeIconAttribute(): string
    {
        $mimeType = $this->mime_type ?? '';
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        
        return match(true) {
            str_contains($mimeType, 'pdf') || $extension === 'pdf' => 'heroicon-o-document',
            str_contains($mimeType, 'image') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) => 'heroicon-o-photo',
            str_contains($mimeType, 'word') || in_array($extension, ['doc', 'docx']) => 'heroicon-o-document-text',
            str_contains($mimeType, 'excel') || in_array($extension, ['xls', 'xlsx']) => 'heroicon-o-table-cells',
            default => 'heroicon-o-document',
        };
    }

    public function getUploadedTimeAgoAttribute(): string
    {
        return $this->uploaded_at ? $this->uploaded_at->diffForHumans() : 'Unknown';
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
            'label' => 'Belum Diverifikasi',
            'color' => 'warning',
            'icon' => 'heroicon-o-clock'
        ];
    }

    // ===== METHODS =====
    
    /**
     * Get document type options for forms
     */
    public static function getDocumentTypeOptions(): array
    {
        return self::DOCUMENT_TYPES;
    }

    /**
     * Verify document
     */
    public function verify(User $verifier, ?string $notes = null): void
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    /**
     * Unverify document
     */
    public function unverify(): void
    {
        $this->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'verification_notes' => null,
        ]);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->delete($this->file_path);
        }
        
        return true;
    }

    /**
     * Check if file exists in storage
     */
    public function fileExists(): bool
    {
        return $this->file_path && Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Get file content for download
     */
    public function getFileContent()
    {
        if ($this->fileExists()) {
            return Storage::disk('public')->get($this->file_path);
        }
        
        return null;
    }

    /**
     * Boot method for model events
     */
    protected static function booted(): void
    {
        // Auto-delete file when record is deleted
        static::deleting(function (EmployeeDocument $document) {
            $document->deleteFile();
        });
    }
}