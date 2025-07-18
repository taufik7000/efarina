<?php

namespace App\Filament\Redaksi\Resources\NewsCategoryResource\Pages;

use App\Filament\Redaksi\Resources\NewsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsCategory extends EditRecord
{
    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}