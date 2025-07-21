<?php

namespace App\Filament\Hrd\Resources\KpiTargetOverrideResource\Pages;

use App\Filament\Hrd\Resources\KpiTargetOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiTargetOverrides extends ListRecords
{
    protected static string $resource = KpiTargetOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
