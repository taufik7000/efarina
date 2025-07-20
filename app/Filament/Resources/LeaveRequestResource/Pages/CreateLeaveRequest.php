<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon; // <-- Tambahkan ini

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    /**
     * Metode ini akan dijalankan sebelum data disimpan ke database.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ubah string tanggal menjadi objek Carbon
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        // Hitung selisih hari dan tambahkan 1 (karena hari pertama dihitung)
        $data['total_days'] = $startDate->diffInDays($endDate) + 1;

        return $data;
    }
}