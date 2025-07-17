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
        
        // Auto-calculate total budget from breakdown
        if (!empty($data['budget_breakdown']) && empty($data['estimasi_budget'])) {
            $total = 0;
            foreach ($data['budget_breakdown'] as $item) {
                $total += $item['amount'] ?? 0;
            }
            $data['estimasi_budget'] = $total;
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Proposal Berhasil Dibuat!')
            ->body('Proposal Anda telah dikirim dan menunggu review dari redaksi.')
            ->success()
            ->duration(6000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}