<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Set created_by ke user yang sedang login
        $data['created_by'] = $user->id;

        // Jika user adalah team (bukan redaksi/admin)
        if ($user->hasRole('team')) {
            // Paksa status ke draft untuk approval
            $data['status'] = 'draft';
            
            // Set project_manager_id ke dirinya sendiri
            $data['project_manager_id'] = $user->id;
            
            // Team tidak bisa isi catatan
            unset($data['catatan']);
        }

        // Jika user redaksi/admin, biarkan mereka pilih PM dan status bisa langsung planning
        if ($user->hasRole(['admin', 'redaksi'])) {
            // Jika tidak ada status yang dipilih, set default ke planning
            if (!isset($data['status']) || empty($data['status'])) {
                $data['status'] = 'planning';
            }
        }

        return $data;
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $user = auth()->user();
        
        if ($user->hasRole(['admin', 'redaksi'])) {
            return Notification::make()
                ->success()
                ->title('Project Dibuat')
                ->body('Project berhasil dibuat dan bisa langsung dimulai.')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Lihat Project')
                        ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->record])),
                ]);
        }

        // Untuk team
        return Notification::make()
            ->success()
            ->title('Proposal Project Terkirim')
            ->body('Proposal project telah dikirim dan menunggu approval dari redaksi.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Proposal')
                    ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->record])),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getFormActions(): array
    {
        $user = auth()->user();
        
        // Label tombol berbeda berdasarkan role
        $createLabel = $user->hasRole('team') ? 'Ajukan Proposal Project' : 'Buat Project';
        
        return [
            $this->getCreateFormAction()
                ->label($createLabel),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    // Override untuk set page title
    public function getTitle(): string
    {
        $user = auth()->user();
        return $user->hasRole('team') ? 'Ajukan Proposal Project' : 'Buat Project Baru';
    }

    // Override breadcrumb
    public function getBreadcrumb(): string
    {
        $user = auth()->user();
        return $user->hasRole('team') ? 'Ajukan Proposal' : 'Buat Project';
    }
}