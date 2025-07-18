<?php

namespace App\Filament\Redaksi\Resources\VideoCategoryResource\Pages;

use App\Filament\Redaksi\Resources\VideoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoCategories extends ListRecords
{
    protected static string $resource = VideoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
