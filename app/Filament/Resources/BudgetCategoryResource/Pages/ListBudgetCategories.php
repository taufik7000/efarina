<?php
// app/Filament/Resources/BudgetCategoryResource/Pages/ListBudgetCategories.php

namespace App\Filament\Resources\BudgetCategoryResource\Pages;

use App\Filament\Resources\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetCategories extends ListRecords
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}