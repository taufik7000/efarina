<?php

namespace App\Filament\Team\Resources\ProjectProposalResource\Pages;

use App\Filament\Team\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectProposal extends EditRecord
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'pending'),
        ];
    }
}