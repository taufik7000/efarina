<?php

namespace App\Filament\Team\Resources\ProjectProposalResource\Pages;

use App\Filament\Team\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectProposal extends ViewRecord
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'pending'),
        ];
    }
}