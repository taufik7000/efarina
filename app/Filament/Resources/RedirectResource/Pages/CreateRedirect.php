<?php

namespace App\Filament\Resources\RedirectResource\Pages;

use App\Filament\Resources\RedirectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateRedirect extends CreateRecord
{
    protected static string $resource = RedirectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Clear cache setelah membuat redirect baru
        $this->clearRedirectCache();
        
        // Notifikasi sukse
    }

    private function clearRedirectCache(): void
    {
        // Clear specific cache atau flush semua cache redirect
        Cache::flush(); // Atau gunakan tag-based cache jika diperlukan
    }
}