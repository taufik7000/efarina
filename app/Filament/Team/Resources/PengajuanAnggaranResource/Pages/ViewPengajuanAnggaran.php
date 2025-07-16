<?php

namespace App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Team\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanAnggaran extends ViewRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }
}