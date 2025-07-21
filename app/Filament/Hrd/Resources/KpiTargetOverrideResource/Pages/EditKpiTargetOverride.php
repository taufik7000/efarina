<?php

namespace App\Filament\Hrd\Resources\KpiTargetOverrideResource\Pages;

use App\Filament\Hrd\Resources\KpiTargetOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiTargetOverride extends EditRecord
{
    protected static string $resource = KpiTargetOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
