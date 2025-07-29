<?php

// app/Filament/Resources/RedirectResource/Pages/ListRedirects.php
namespace App\Filament\Resources\RedirectResource\Pages;

use App\Filament\Resources\RedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListRedirects extends ListRecords
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->action(function (array $data) {
                    // Logic untuk import CSV jika diperlukan
                })
                ->hidden(), // Sembunyikan jika tidak digunakan
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widget untuk statistik redirect jika diperlukan
        ];
    }
}
