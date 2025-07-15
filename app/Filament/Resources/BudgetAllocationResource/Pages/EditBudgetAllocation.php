<?php
// app/Filament/Resources/BudgetAllocationResource/Pages/EditBudgetAllocation.php

namespace App\Filament\Resources\BudgetAllocationResource\Pages;

use App\Filament\Resources\BudgetAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetAllocation extends EditRecord
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur'])),
        ];
    }

    // Jika ada masalah dengan validation, override ini
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Pastikan used_amount tidak ikut ter-update dari form
        unset($data['used_amount']);
        unset($data['remaining_amount']);
        unset($data['usage_percentage']);
        
        return $data;
    }
}