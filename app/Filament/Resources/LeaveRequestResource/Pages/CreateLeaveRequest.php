<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hitung total hari kerja
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            
            $totalDays = 0;
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Hitung hanya hari kerja (Senin-Jumat)
                if ($date->isWeekday()) {
                    $totalDays++;
                }
            }
            
            $data['total_days'] = $totalDays;
        }

        // Set default status dan replacement_status
        $data['status'] = 'pending';
        if (isset($data['replacement_user_id']) && $data['replacement_user_id']) {
            $data['replacement_status'] = 'pending';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $leaveRequest = $this->record;

        // KIRIM NOTIFIKASI KE HRD/ADMIN menggunakan Filament native
        $hrdUsers = User::role(['hrd', 'admin'])->get();
        
        // Jika tidak ada user HRD/admin, kirim ke semua user untuk testing
        if ($hrdUsers->isEmpty()) {
            $hrdUsers = User::all();
        }

        foreach ($hrdUsers as $hrd) {
            \Filament\Notifications\Notification::make()
                ->title('Pengajuan Cuti Baru')
                ->body("{$leaveRequest->user->name} mengajukan {$leaveRequest->leave_type} dari {$leaveRequest->start_date->format('d M Y')} sampai {$leaveRequest->end_date->format('d M Y')}")
                ->icon('heroicon-o-document-arrow-up')
                ->success()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('review')
                        ->label('Review Pengajuan')
                        ->url('/hrd/leave-request-managements')
                ])
                ->sendToDatabase($hrd);
        }

        // KIRIM NOTIFIKASI KE PENGGANTI JIKA ADA
        if ($leaveRequest->replacement_user_id) {
            $replacementUser = $leaveRequest->replacementUser;
            
            \Filament\Notifications\Notification::make()
                ->title('Permintaan Menjadi Pengganti')
                ->body("{$leaveRequest->user->name} meminta Anda menjadi pengganti selama cuti {$leaveRequest->leave_type}")
                ->icon('heroicon-o-user-plus')
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('review')
                        ->label('Lihat Detail')
                        ->url('/pengajuan-cuti')
                ])
                ->sendToDatabase($replacementUser);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}