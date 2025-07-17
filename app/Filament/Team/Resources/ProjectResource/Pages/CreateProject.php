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
        // Set created_by ke user yang sedang login
        $data['created_by'] = auth()->id();

        // Jika user bukan redaksi/admin, paksa status ke draft
        if (!auth()->user()->hasRole(['admin', 'redaksi'])) {
            $data['status'] = 'draft';
            unset($data['catatan']); // Team tidak bisa isi catatan
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
                ->body('Project berhasil dibuat dan bisa langsung dimulai.');
        }

        return Notification::make()
            ->success()
            ->title('Project Proposal Submitted')
            ->body('Project proposal telah dikirim dan menunggu approval dari redaksi.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Project')
                    ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->record])),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat Project'),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }
}