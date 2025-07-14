<?php

namespace App\Filament\Hrd\Resources\KehadiranResource\Pages;

use App\Filament\Hrd\Resources\KehadiranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKehadiran extends EditRecord
{
    protected static string $resource = KehadiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
