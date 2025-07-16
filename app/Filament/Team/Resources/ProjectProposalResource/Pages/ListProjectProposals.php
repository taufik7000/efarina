<?php

namespace App\Filament\Team\Resources\ProjectProposalResource\Pages;

use App\Filament\Team\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectProposals extends ListRecords
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Proposal Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
