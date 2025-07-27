<?php

namespace App\Filament\Resources\BudgetPlanResource\Pages;

use App\Filament\Resources\BudgetPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetPlan extends EditRecord
{
    protected static string $resource = BudgetPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
