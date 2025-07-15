<?php

namespace App\Filament\Resources\BudgetSubcategoryResource\Pages;

use App\Filament\Resources\BudgetSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetSubcategories extends ListRecords
{
    protected static string $resource = BudgetSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
