<?php

namespace App\Filament\Resources\BudgetPeriodResource\Pages;

use App\Filament\Resources\BudgetPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetPeriods extends ListRecords
{
    protected static string $resource = BudgetPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
