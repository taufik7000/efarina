<?php

namespace App\Filament\Hrd\Resources\LeaveRequestManagementResource\Pages;

use App\Filament\Hrd\Resources\LeaveRequestManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeaveRequestManagement extends ListRecords
{
    protected static string $resource = LeaveRequestManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
