<?php

namespace App\Filament\Redaksi\Resources\ProjectProposalResource\Pages;

use App\Filament\Redaksi\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectProposals extends ListRecords
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
