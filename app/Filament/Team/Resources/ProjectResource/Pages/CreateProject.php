<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by ke user yang sedang login
        $data['created_by'] = auth()->id();
        
        // Set project_manager_id jika belum di-set
        if (!isset($data['project_manager_id'])) {
            $data['project_manager_id'] = auth()->id();
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}