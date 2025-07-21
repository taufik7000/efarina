<?php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmployeeProfile extends EditRecord
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Profile'),


            Actions\DeleteAction::make()
                ->label('Hapus Karyawan')
                ->requiresConfirmation()
                ->modalDescription('Menghapus karyawan akan menghapus semua data terkait. Aksi ini tidak dapat dibatalkan!')
                ->visible(fn () => auth()->user()->hasRole(['admin', 'direktur'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Profile Berhasil Diperbarui')
            ->body('Data profile karyawan telah disimpan.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Profile')
                    ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()])),
                
                \Filament\Notifications\Actions\Action::make('documents')
                    ->label('Kelola Dokumen')
                    ->url($this->getResource()::getUrl('documents', ['record' => $this->getRecord()])),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan employee profile exists
        $this->getRecord()->getOrCreateProfile();
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Log activity atau trigger events jika perlu
        // Misalnya: activity log, send notification, dll
    }
}