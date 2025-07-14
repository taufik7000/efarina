<?php

namespace App\Filament\Hrd\Resources\KaryawanJabatanResource\Pages;

use App\Filament\Hrd\Resources\KaryawanJabatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKaryawanJabatan extends EditRecord
{
    protected static string $resource = KaryawanJabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
