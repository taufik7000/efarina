<?php

// app/Filament/Hrd/Resources/EmployeeDocumentResource/Pages/CreateEmployeeDocument.php
// UNTUK FILAMENT 3.x

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use App\Models\EmployeeDocument;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Di Filament 3.x, file upload sudah otomatis di-handle
        // File path sudah berupa string path, bukan UploadedFile object
        
        if (isset($data['file_upload']) && $data['file_upload']) {
            try {
                // Di Filament 3.x, $data['file_upload'] sudah berupa path string
                $filePath = $data['file_upload'];
                
                // Get file info dari storage
                $fullPath = Storage::disk('public')->path($filePath);
                $fileName = basename($filePath);
                
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    $mimeType = mime_content_type($fullPath);
                } else {
                    $fileSize = null;
                    $mimeType = null;
                }

                // Check if document already exists for this user and type
                $existingDoc = EmployeeDocument::where('user_id', $data['user_id'])
                    ->where('document_type', $data['document_type'])
                    ->first();

                if ($existingDoc) {
                    // Delete old file and record
                    if (Storage::disk('public')->exists($existingDoc->file_path)) {
                        Storage::disk('public')->delete($existingDoc->file_path);
                    }
                    $existingDoc->delete();

                    Notification::make()
                        ->title('Dokumen Diganti')
                        ->body('Dokumen lama telah diganti dengan yang baru.')
                        ->warning()
                        ->send();
                }

                // Prepare data for creation - pastikan semua data ter-sanitize
                $data['file_path'] = (string) $filePath;
                $data['file_name'] = (string) $fileName;
                $data['file_size'] = $fileSize ? (int) $fileSize : null;
                $data['mime_type'] = $mimeType ? (string) $mimeType : null;
                $data['uploaded_at'] = now();
                
                // Pastikan document_type adalah string yang valid
                $data['document_type'] = (string) $data['document_type'];
                
                // Pastikan user_id adalah integer
                $data['user_id'] = (int) $data['user_id'];

                // Set verification data if verified
                if ($data['is_verified'] ?? false) {
                    $data['verified_at'] = now();
                    $data['verified_by'] = (int) auth()->id();
                    $data['is_verified'] = true;
                } else {
                    $data['is_verified'] = false;
                    $data['verified_at'] = null;
                    $data['verified_by'] = null;
                }

                // Remove file_upload from data (Filament 3.x requirement)
                unset($data['file_upload']);
                
            } catch (\Exception $e) {
                // Log error
                \Log::error('File upload error in Filament 3.x: ' . $e->getMessage());
                
                // Show notification
                Notification::make()
                    ->title('Upload Gagal')
                    ->body('Error: ' . $e->getMessage())
                    ->danger()
                    ->send();
                
                // Stop creation
                $this->halt();
            }
        } else {
            // No file uploaded
            Notification::make()
                ->title('File Diperlukan')
                ->body('Silakan pilih file untuk diupload.')
                ->warning()
                ->send();
                
            $this->halt();
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('✅ Dokumen Berhasil Diupload')
            ->body('Dokumen karyawan telah berhasil diupload dan disimpan.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Daftar')
                    ->url($this->getResource()::getUrl('index')),
            ]);
    }

    protected function afterCreate(): void
    {
        // Optional: Log activity atau trigger events
        \Log::info('Document uploaded', [
            'user_id' => $this->record->user_id,
            'document_type' => $this->record->document_type,
            'file_name' => $this->record->file_name,
            'uploaded_by' => auth()->id(),
        ]);
    }
}