<?php

namespace App\Filament\Team\Pages;

use App\Models\EmployeeDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class MyDocuments extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.team.pages.my-documents';

    protected static ?string $navigationGroup = 'Profile Saya';
    protected static ?string $navigationLabel = 'Dokumen Saya';
    protected static ?string $title = 'Dokumen Saya';
    protected static ?int $navigationSort = 3;

    public Collection $documents;

    public function mount(): void
    {
        $this->loadDocuments();
    }

    protected function loadDocuments(): void
    {
        // Memuat hanya dokumen milik user yang sedang login
        $this->documents = EmployeeDocument::where('user_id', Auth::id())
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    /**
     * Aksi untuk membuka modal upload dokumen.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadDocument')
                ->label('Upload Dokumen Baru')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Upload Dokumen')
                ->modalWidth('lg')
                ->form([
                    Select::make('document_type')
                        ->label('Jenis Dokumen')
                        ->options([
                            'ktp' => 'KTP',
                            'npwp' => 'NPWP',
                            'cv' => 'CV / Resume',
                            'ijazah' => 'Ijazah Pendidikan Terakhir',
                        ])
                        ->required(),
                    FileUpload::make('file_upload')
                        ->label('File Dokumen')
                        ->directory('employee-documents')
                        ->disk('public')
                        ->maxSize(5120) // 5MB
                        ->required()
                        ->helperText('Format: PDF, JPG, PNG. Maksimal 5MB.'),
                    Textarea::make('description')
                        ->label('Keterangan (Opsional)')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->processDocumentUpload($data);
                }),
        ];
    }
    
    /**
     * Proses penyimpanan dokumen yang diupload.
     */
    private function processDocumentUpload(array $data): void
    {
        try {
            $user = Auth::user();
            $filePath = $data['file_upload'];

            // Cek apakah dokumen dengan tipe yang sama sudah ada, jika ada, ganti.
            $existingDoc = EmployeeDocument::where('user_id', $user->id)
                ->where('document_type', $data['document_type'])
                ->first();

            if ($existingDoc) {
                // Hapus file lama sebelum mengganti
                if (Storage::disk('public')->exists($existingDoc->file_path)) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }
                $existingDoc->delete();
            }

            // Dapatkan informasi file
            $fullPath = Storage::disk('public')->path($filePath);
            $fileName = basename($filePath);
            $fileSize = file_exists($fullPath) ? filesize($fullPath) : null;
            $mimeType = file_exists($fullPath) ? mime_content_type($fullPath) : null;

            EmployeeDocument::create([
                'user_id' => $user->id,
                'document_type' => $data['document_type'],
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'description' => $data['description'],
                'uploaded_at' => now(),
                'is_verified' => false, // Dokumen baru selalu butuh verifikasi
            ]);

            Notification::make()
                ->title('Upload Berhasil')
                ->body('Dokumen Anda telah diupload dan menunggu verifikasi dari HRD.')
                ->success()
                ->send();
            
            // Muat ulang daftar dokumen
            $this->loadDocuments();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Upload Gagal')
                ->body('Terjadi kesalahan saat mengupload dokumen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

}