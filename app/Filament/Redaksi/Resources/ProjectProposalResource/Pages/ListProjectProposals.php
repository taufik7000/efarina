<?php

// File: app/Filament/Redaksi/Resources/ProjectProposalResource/Pages/ListProjectProposals.php

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
            // Redaksi biasanya tidak create proposal, tapi bisa ditambahkan jika perlu
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa tambahkan widget statistik proposal di sini
        ];
    }
}