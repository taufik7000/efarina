<?php

namespace App\Filament\Redaksi\Resources\ProjectProposalResource\Pages;

use App\Filament\Redaksi\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectProposal extends EditRecord
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
