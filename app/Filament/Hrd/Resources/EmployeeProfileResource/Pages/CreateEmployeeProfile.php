<?php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeProfile extends CreateRecord
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-create employee profile jika belum ada
        return $data;
    }

    protected function afterCreate(): void
    {
        // Create empty employee profile
        $this->getRecord()->getOrCreateProfile();
        
        $this->notify('success', 'Profile karyawan berhasil dibuat! Silakan lengkapi data personal.');
    }
}