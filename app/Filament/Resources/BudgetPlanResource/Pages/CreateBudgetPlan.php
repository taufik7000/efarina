<?php
// app/Filament/Resources/BudgetPlanResource/Pages/CreateBudgetPlan.php

namespace App\Filament\Resources\BudgetPlanResource\Pages;

use App\Filament\Resources\BudgetPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetPlan extends CreateRecord
{
    protected static string $resource = BudgetPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}