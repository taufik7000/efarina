<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['progress_percentage'] = 0;
        
        // Status selalu draft saat dibuat, nanti bisa diubah manual
        $data['status'] = 'draft';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        $message = "Project '{$record->nama_project}' telah dibuat";
        
        if ($record->pengajuan_anggaran_id) {
            $pengajuan = $record->pengajuanAnggaran;
            $message .= " dengan anggaran Rp " . number_format($pengajuan->total_anggaran, 0, ',', '.') . " dari pengajuan yang sudah disetujui.";
        } else {
            $message .= " tanpa anggaran khusus.";
        }
        
        Notification::make()
            ->title('Project berhasil dibuat!')
            ->body($message)
            ->success()
            ->duration(6000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}