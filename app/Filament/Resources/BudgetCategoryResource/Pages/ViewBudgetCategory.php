<?php

namespace App\Filament\Resources\BudgetCategoryResource\Pages;

use App\Filament\Resources\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBudgetCategory extends ViewRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}