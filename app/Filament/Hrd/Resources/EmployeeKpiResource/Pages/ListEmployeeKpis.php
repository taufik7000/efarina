<?php

namespace App\Filament\Hrd\Resources\EmployeeKpiResource\Pages;

use App\Filament\Hrd\Resources\EmployeeKpiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeKpis extends ListRecords
{
    protected static string $resource = EmployeeKpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
