<?php

namespace App\Filament\Hrd\Resources\LeaveRequestManagementResource\Pages;

use App\Filament\Hrd\Resources\LeaveRequestManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveRequestManagement extends EditRecord
{
    protected static string $resource = LeaveRequestManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
