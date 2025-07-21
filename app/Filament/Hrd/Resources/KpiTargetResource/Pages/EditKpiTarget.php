<?php

namespace App\Filament\Hrd\Resources\KpiTargetResource\Pages;

use App\Filament\Hrd\Resources\KpiTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiTarget extends EditRecord
{
    protected static string $resource = KpiTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
