<?php

// app/Filament/Hrd/Resources/EmployeeDocumentResource/Pages/EditEmployeeDocument.php
// UNTUK FILAMENT 3.x

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class EditEmployeeDocument extends EditRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview File')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => Storage::disk('public')->url($this->record->file_path))
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    $extension = strtolower(pathinfo($this->record->file_name, PATHINFO_EXTENSION));
                    return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
                }),

            Actions\Action::make('download')
                ->label('Download File')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    if (Storage::disk('public')->exists($this->record->file_path)) {
                        return Storage::disk('public')->download($this->record->file_path, $this->record->file_name);
                    }
                    
                    Notification::make()
                        ->title('File Tidak Ditemukan')
                        ->body('File dokumen tidak dapat ditemukan di server.')
                        ->danger()
                        ->send();
                }),

            Actions\Action::make('replace_file')
                ->label('Ganti File')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('new_file')
                        ->label('File Baru')
                        ->directory('employee-documents')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->helperText('Maksimal 5MB'),

                    \Filament\Forms\Components\Textarea::make('replacement_reason')
                        ->label('Alasan Penggantian')
                        ->placeholder('Alasan mengganti dokumen...')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    try {
                        // Hapus file lama
                        if (Storage::disk('public')->exists($this->record->file_path)) {
                            Storage::disk('public')->delete($this->record->file_path);
                        }

                        // Di Filament 3.x, file sudah ter-upload otomatis
                        $newFilePath = $data['new_file'];
                        
                        // Get file info
                        $fullPath = Storage::disk('public')->path($newFilePath);
                        $fileName = basename($newFilePath);
                        
                        if (file_exists($fullPath)) {
                            $fileSize = filesize($fullPath);
                            $mimeType = mime_content_type($fullPath);
                        } else {
                            $fileSize = null;
                            $mimeType = null;
                        }

                        // Update record
                        $this->record->update([
                            'file_path' => $newFilePath,
                            'file_name' => $fileName,
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                            'description' => $data['replacement_reason'],
                            'is_verified' => false, // Reset verification
                            'verified_at' => null,
                            'verified_by' => null,
                            'verification_notes' => null,
                            'uploaded_at' => now(),
                        ]);

                        Notification::make()
                            ->title('File Berhasil Diganti')
                            ->body('Dokumen telah diganti dengan file baru.')
                            ->success()
                            ->send();

                        // Refresh the page
                        return redirect()->to($this->getResource()::getUrl('edit', ['record' => $this->record]));

                    } catch (\Exception $e) {
                        \Log::error('File replacement error: ' . $e->getMessage());
                        
                        Notification::make()
                            ->title('Gagal Mengganti File')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('toggle_verification')
                ->label(fn (): string => $this->record->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi Dokumen')
                ->icon(fn (): string => $this->record->is_verified ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (): string => $this->record->is_verified ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->record->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi Dokumen')
                ->modalDescription(fn (): string => $this->record->is_verified 
                    ? 'Apakah Anda yakin ingin membatalkan verifikasi dokumen ini?' 
                    : 'Apakah Anda yakin dokumen ini valid dan dapat diverifikasi?'
                )
                ->action(function () {
                    if ($this->record->is_verified) {
                        // Unverify
                        $this->record->update([
                            'is_verified' => false,
                            'verified_by' => null,
                            'verified_at' => null,
                            'verification_notes' => null,
                        ]);

                        Notification::make()
                            ->title('Verifikasi Dibatalkan')
                            ->body('Verifikasi dokumen telah dibatalkan.')
                            ->success()
                            ->send();
                    } else {
                        // Verify
                        $this->record->update([
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Dokumen Terverifikasi')
                            ->body('Dokumen telah berhasil diverifikasi.')
                            ->success()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make()
                ->before(function () {
                    // Hapus file dari storage sebelum hapus record
                    if (Storage::disk('public')->exists($this->record->file_path)) {
                        Storage::disk('public')->delete($this->record->file_path);
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Remove file upload field dari form edit (tidak perlu ditampilkan)
        unset($data['file_upload']);
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jangan override file path yang sudah ada
        if (isset($data['file_upload'])) {
            unset($data['file_upload']);
        }
        
        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Dokumen Berhasil Diperbarui')
            ->body('Data dokumen karyawan telah disimpan.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_list')
                    ->label('Lihat Daftar')
                    ->url($this->getResource()::getUrl('index')),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}