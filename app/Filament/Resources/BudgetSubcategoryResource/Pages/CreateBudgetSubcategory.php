<?php
// app/Filament/Resources/BudgetSubcategoryResource/Pages/CreateBudgetSubcategory.php

namespace App\Filament\Resources\BudgetSubcategoryResource\Pages;

use App\Filament\Resources\BudgetSubcategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetSubcategory extends CreateRecord
{
    protected static string $resource = BudgetSubcategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}