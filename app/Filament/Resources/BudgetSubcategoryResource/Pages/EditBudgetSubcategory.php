<?php

namespace App\Filament\Resources\BudgetSubcategoryResource\Pages;

use App\Filament\Resources\BudgetSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetSubcategory extends EditRecord
{
    protected static string $resource = BudgetSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
