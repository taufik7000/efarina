<?php

namespace App\Filament\Hrd\Resources\KehadiranResource\Pages;

use App\Filament\Hrd\Resources\KehadiranResource;
use Filament\Resources\Pages\ListRecords;

class ListKehadirans extends ListRecords
{
    protected static string $resource = KehadiranResource::class;

    // Metode getTableRecords() sudah tidak diperlukan dan bisa dihapus.
    // Logika pengambilan data sekarang ditangani oleh metode query() di dalam Resource.

    protected function getHeaderActions(): array
    {
        return [];
    }
}