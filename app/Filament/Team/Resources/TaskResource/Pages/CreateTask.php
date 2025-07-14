<?php

namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
    protected static ?string $navigationLabel = 'Buat Tugas';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}