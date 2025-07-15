<?php

namespace App\Filament\Resources\BudgetAllocationResource\Pages;

use App\Filament\Resources\BudgetAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBudgetAllocation extends ViewRecord
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),
        ];
    }
}