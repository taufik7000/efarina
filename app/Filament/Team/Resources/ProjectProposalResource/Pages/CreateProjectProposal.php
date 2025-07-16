<?php

// CreateProjectProposal.php
namespace App\Filament\Team\Resources\ProjectProposalResource\Pages;

use App\Filament\Team\Resources\ProjectProposalResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProjectProposal extends CreateRecord
{
    protected static string $resource = ProjectProposalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Proposal Berhasil Dibuat!')
            ->body('Proposal Anda telah dikirim dan menunggu review dari redaksi.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}