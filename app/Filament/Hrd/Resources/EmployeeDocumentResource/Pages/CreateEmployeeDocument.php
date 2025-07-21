<?php

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use App\Models\EmployeeDocument;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle file upload
        if (isset($data['file_upload'])) {
            $uploadedFile = $data['file_upload'];
            $filePath = $uploadedFile->store('employee-documents', 'public');

            // Check if document already exists for this user and type
            $existingDoc = EmployeeDocument::where('user_id', $data['user_id'])
                ->where('document_type', $data['document_type'])
                ->first();

            if ($existingDoc) {
                // Delete old file and record
                $existingDoc->deleteFile();
                $existingDoc->delete();

                Notification::make()
                    ->title('Dokumen Diganti')
                    ->body('Dokumen lama telah diganti dengan yang baru.')
                    ->warning()
                    ->send();
            }

            // Prepare data for creation
            $data['file_path'] = $filePath;
            $data['file_name'] = $uploadedFile->getClientOriginalName();
            $data['file_size'] = $uploadedFile->getSize();
            $data['mime_type'] = $uploadedFile->getMimeType();
            $data['uploaded_at'] = now();

            // Set verification data if verified
            if ($data['is_verified'] ?? false) {
                $data['verified_at'] = now();
                $data['verified_by'] = auth()->id();
            }

            // Remove file_upload from data
            unset($data['file_upload']);
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Dokumen Berhasil Diupload')
            ->body('Dokumen karyawan telah berhasil diupload dan disimpan.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Daftar')
                    ->url($this->getResource()::getUrl('index')),
            ]);
    }
}