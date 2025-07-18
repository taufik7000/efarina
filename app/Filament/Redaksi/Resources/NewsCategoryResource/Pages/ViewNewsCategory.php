<?php

namespace App\Filament\Redaksi\Resources\NewsCategoryResource\Pages;

use App\Filament\Redaksi\Resources\NewsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsCategory extends ViewRecord
{
    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}