<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon; // <-- Tambahkan ini

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    /**
     * Metode ini akan dijalankan sebelum data yang diedit disimpan.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $data['total_days'] = $startDate->diffInDays($endDate) + 1;

        return $data;
    }
}