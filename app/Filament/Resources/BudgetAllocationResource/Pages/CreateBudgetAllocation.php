<?php
// app/Filament/Resources/BudgetAllocationResource/Pages/CreateBudgetAllocation.php

namespace App\Filament\Resources\BudgetAllocationResource\Pages;

use App\Filament\Resources\BudgetAllocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetAllocation extends CreateRecord
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}