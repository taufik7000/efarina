<?php

namespace App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Team\Resources\PengajuanAnggaranResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPengajuanAnggaran extends EditRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // PENTING: Recalculate total_anggaran saat edit
        if (isset($data['detail_items']) && is_array($data['detail_items'])) {
            $total = 0;
            
            foreach ($data['detail_items'] as $item) {
                $total += (float) ($item['total_price'] ?? 0);
            }
            
            $data['total_anggaran'] = $total;
            
            // Debug log (optional)
            \Log::info('Pengajuan Anggaran Updated:', [
                'id' => $this->record->id,
                'total_calculated' => $total,
                'items_count' => count($data['detail_items']),
            ]);
        } else {
            $data['total_anggaran'] = 0;
        }
        
        return $data;
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengajuan Anggaran Diperbarui')
            ->body("Pengajuan anggaran berhasil diperbarui dengan total Rp " . number_format($this->record->total_anggaran, 0, ',', '.'));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    // Authorization
    protected function authorizeAccess(): void
    {
        // Hanya creator yang bisa edit, dan hanya jika masih draft
        if ($this->record->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pengajuan ini.');
        }
        
        if (!in_array($this->record->status, ['draft', 'rejected'])) {
            abort(403, 'Pengajuan ini tidak dapat diedit karena sudah dalam proses approval.');
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'),
            $this->getCancelFormAction(),
        ];
    }
}