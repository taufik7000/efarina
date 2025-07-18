<?php

namespace App\Filament\Redaksi\Resources\NewsCategoryResource\Pages;

use App\Filament\Redaksi\Resources\NewsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsCategories extends ListRecords
{
    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Kategori Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
