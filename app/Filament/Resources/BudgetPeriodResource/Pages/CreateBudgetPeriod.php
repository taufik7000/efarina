<?php
// app/Filament/Resources/BudgetPeriodResource/Pages/CreateBudgetPeriod.php

namespace App\Filament\Resources\BudgetPeriodResource\Pages;

use App\Filament\Resources\BudgetPeriodResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetPeriod extends CreateRecord
{
    protected static string $resource = BudgetPeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}