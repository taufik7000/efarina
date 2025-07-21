<?php

namespace App\Filament\Hrd\Resources\LeaveRequestManagementResource\Pages;

use App\Filament\Hrd\Resources\LeaveRequestManagementResource;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListLeaveRequestManagement extends ListRecords
{
    protected static string $resource = LeaveRequestManagementResource::class;
    protected static string $view = 'filament.hrd.pages.list-leave-request-management';

    // Data untuk view
    public $statistics = [];
    public $pendingRequests = [];
    public $recentRequests = [];
    public $selectedFilter = 'pending';

    public function mount(): void
    {
        parent::mount();
        $this->loadStatistics();
        $this->loadRequests();
    }

    protected function loadStatistics(): void
    {
        $this->statistics = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'today' => LeaveRequest::whereDate('created_at', today())->count(),
            'this_month' => LeaveRequest::whereMonth('created_at', now()->month)->count(),
        ];
    }

    protected function loadRequests(): void
    {
        // Load berdasarkan filter yang dipilih
        $query = LeaveRequest::with(['user.jabatan', 'replacementUser', 'approver'])
            ->when($this->selectedFilter !== 'all', function ($q) {
                $q->where('status', $this->selectedFilter);
            })
            ->orderBy('created_at', 'desc');

        $this->pendingRequests = $query->get();

        // Recent requests untuk timeline
        $this->recentRequests = LeaveRequest::with(['user', 'approver'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function filterRequests($filter): void
    {
        $this->selectedFilter = $filter;
        $this->loadRequests();
    }

    public function approveRequest($requestId): void
    {
        $request = LeaveRequest::find($requestId);
        
        if (!$request || $request->status !== 'pending') {
            $this->dispatch('notify', 'error', 'Pengajuan tidak valid atau sudah diproses');
            return;
        }

        // Cek jika ada pengganti dan belum disetujui
        if ($request->replacement_user_id && $request->replacement_status !== 'approved') {
            $this->dispatch('notify', 'error', 'Pengganti belum menyetujui permintaan');
            return;
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Buat kompensasi untuk pengganti jika ada
        if ($request->replacement_user_id) {
            \App\Models\Compensation::create([
                'user_id' => $request->replacement_user_id,
                'work_date' => $request->start_date,
                'work_hours' => 8,
                'work_reason' => 'Kompensasi menggantikan cuti ' . $request->user->name,
                'status' => 'earned',
                'expires_at' => \Carbon\Carbon::now()->endOfMonth(),
                'created_by' => auth()->id()
            ]);
        }

        // Update kehadiran
        for ($date = $request->start_date->copy(); $date->lte($request->end_date); $date->addDay()) {
            \App\Models\Kehadiran::updateOrCreate(
                ['user_id' => $request->user_id, 'tanggal' => $date],
                ['status' => 'Cuti', 'leave_request_id' => $request->id]
            );
        }

        // KIRIM NOTIFIKASI KE PENGAJU
        \Filament\Notifications\Notification::make()
            ->title('Cuti Disetujui')
            ->body("Pengajuan {$request->leave_type} Anda telah disetujui")
            ->icon('heroicon-o-check-circle')
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->url(url('/team/pengajuan-cuti'))
            ])
            ->sendToDatabase($request->user);

        // KIRIM NOTIFIKASI KE PENGGANTI JIKA ADA
        if ($request->replacement_user_id) {
            \Filament\Notifications\Notification::make()
                ->title('Cuti Disetujui - Anda Ditunjuk Pengganti')
                ->body("Cuti {$request->user->name} disetujui. Anda ditunjuk sebagai pengganti dari {$request->start_date->format('d M Y')} sampai {$request->end_date->format('d M Y')}")
                ->icon('heroicon-o-user-plus')
                ->info()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Lihat Detail')
                        ->url(url('/team/pengajuan-cuti'))
                ])
                ->sendToDatabase($request->replacementUser);
        }

        $this->dispatch('notify', 'success', 'Pengajuan cuti berhasil disetujui');
        $this->loadStatistics();
        $this->loadRequests();
    }

    public function rejectRequest($requestId, $reason): void
    {
        $request = LeaveRequest::find($requestId);
        
        if (!$request || $request->status !== 'pending') {
            return;
        }

        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // KIRIM NOTIFIKASI KE PENGAJU
        \Filament\Notifications\Notification::make()
            ->title('Cuti Ditolak')
            ->body("Pengajuan {$request->leave_type} Anda ditolak: {$reason}")
            ->icon('heroicon-o-x-circle')
            ->danger()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->url(url('/team/pengajuan-cuti'))
            ])
            ->sendToDatabase($request->user);

        // KIRIM NOTIFIKASI KE PENGGANTI JIKA ADA (bahwa tidak jadi menggantikan)
        if ($request->replacement_user_id) {
            \Filament\Notifications\Notification::make()
                ->title('Pengajuan Cuti Ditolak')
                ->body("Pengajuan cuti {$request->user->name} ditolak. Anda tidak perlu menggantikan.")
                ->icon('heroicon-o-information-circle')
                ->info()
                ->sendToDatabase($request->replacementUser);
        }

        $this->dispatch('notify', 'success', 'Pengajuan cuti berhasil ditolak');
        $this->loadStatistics();
        $this->loadRequests();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Remove create action karena HRD tidak membuat pengajuan cuti
        ];
    }

    protected function getViewData(): array
    {
        return [
            'statistics' => $this->statistics,
            'pendingRequests' => $this->pendingRequests,
            'recentRequests' => $this->recentRequests,
            'selectedFilter' => $this->selectedFilter,
        ];
    }
}