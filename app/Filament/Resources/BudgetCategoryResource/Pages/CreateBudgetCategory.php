<?php
// app/Filament/Resources/BudgetCategoryResource/Pages/CreateBudgetCategory.php

namespace App\Filament\Resources\BudgetCategoryResource\Pages;

use App\Filament\Resources\BudgetCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetCategory extends CreateRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}