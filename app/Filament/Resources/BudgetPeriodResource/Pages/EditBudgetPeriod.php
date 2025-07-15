<?php

namespace App\Filament\Resources\BudgetPeriodResource\Pages;

use App\Filament\Resources\BudgetPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetPeriod extends EditRecord
{
    protected static string $resource = BudgetPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
