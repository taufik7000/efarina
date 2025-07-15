<?php

namespace App\Filament\Resources\BudgetCategoryResource\Pages;

use App\Filament\Resources\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetCategory extends EditRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}