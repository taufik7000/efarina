<?php
namespace App\Filament\Resources\BudgetAllocationResource\Pages;

use App\Filament\Resources\BudgetAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetAllocations extends ListRecords
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}