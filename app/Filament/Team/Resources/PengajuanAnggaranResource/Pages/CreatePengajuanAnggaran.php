<?php

namespace App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Team\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePengajuanAnggaran extends CreateRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by to current user
        $data['created_by'] = auth()->id();
        
        // Set default values
        $data['status'] = 'draft';
        $data['redaksi_approval_status'] = 'pending';
        $data['keuangan_approval_status'] = 'pending';
        $data['tanggal_pengajuan'] = now();
        $data['realisasi_anggaran'] = 0;
        $data['sisa_anggaran'] = $data['total_anggaran'] ?? 0;
        $data['is_used'] = false;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Show notification
        Notification::make()
            ->title('Pengajuan Anggaran Berhasil Dibuat!')
            ->body("Pengajuan '{$record->judul_pengajuan}' telah dibuat sebagai draft. Anda dapat mengajukan untuk masuk ke workflow approval.")
            ->success()
            ->duration(8000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}