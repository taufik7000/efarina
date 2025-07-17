<?php

namespace App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Team\Resources\PengajuanAnggaranResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePengajuanAnggaran extends CreateRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by
        $data['created_by'] = auth()->id();
        
        // Set default status
        $data['status'] = 'draft';
        
        // PENTING: Hitung total_anggaran dari detail_items
        if (isset($data['detail_items']) && is_array($data['detail_items'])) {
            $total = 0;
            
            foreach ($data['detail_items'] as $item) {
                $total += (float) ($item['total_price'] ?? 0);
            }
            
            $data['total_anggaran'] = $total;
            
            // Debug log (optional)
            \Log::info('Pengajuan Anggaran Created:', [
                'total_calculated' => $total,
                'items_count' => count($data['detail_items']),
                'items' => $data['detail_items']
            ]);
        } else {
            $data['total_anggaran'] = 0;
        }
        
        // Generate nomor pengajuan jika belum ada
        if (empty($data['nomor_pengajuan'])) {
            $data['nomor_pengajuan'] = $this->generateNomorPengajuan();
        }
        
        // Set tanggal pengajuan
        $data['tanggal_pengajuan'] = now();
        
        return $data;
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengajuan Anggaran Dibuat')
            ->body("Pengajuan anggaran berhasil dibuat dengan total Rp " . number_format($this->record->total_anggaran, 0, ',', '.'))
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Pengajuan')
                    ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->record])),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    private function generateNomorPengajuan(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Ambil nomor terakhir bulan ini
        $lastNumber = \App\Models\PengajuanAnggaran::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('nomor_pengajuan')
            ->count();
        
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return "PA/{$year}/{$month}/{$nextNumber}";
    }

    // Override untuk form actions
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Pengajuan'),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }
}