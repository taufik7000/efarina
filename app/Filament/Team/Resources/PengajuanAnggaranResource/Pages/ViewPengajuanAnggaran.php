<?php

namespace App\Filament\Team\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Team\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanAnggaran extends ViewRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected static string $view = 'filament.team.pages.view-pengajuan-anggaran';

    // Form properties untuk modal
    public $redaksi_notes = '';
    public $keuangan_notes = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => 
                    $this->record->created_by === auth()->id() && 
                    in_array($this->record->status, ['draft', 'rejected'])
                ),

            Actions\Action::make('duplicate')
                ->label('Duplikat')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $newRecord = $this->record->replicate([
                        'nomor_pengajuan',
                        'status',
                        'redaksi_approved_by',
                        'redaksi_approved_at', 
                        'redaksi_notes',
                        'keuangan_approved_by',
                        'keuangan_approved_at',
                        'keuangan_notes',
                    ]);
                    
                    $newRecord->judul_pengajuan = $this->record->judul_pengajuan . ' (Copy)';
                    $newRecord->status = 'draft';
                    $newRecord->save();
                    
                    Notification::make()
                        ->title('Pengajuan Diduplikat')
                        ->body('Pengajuan berhasil diduplikat sebagai draft baru.')
                        ->success()
                        ->send();
                        
                    return redirect(static::getResource()::getUrl('edit', ['record' => $newRecord]));
                }),
        ];
    }

    // Livewire methods untuk approval actions
    public function approveRedaksi()
    {
        if (!auth()->user()->hasRole(['redaksi', 'admin']) || $this->record->status !== 'pending_redaksi') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk melakukan tindakan ini.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('open-modal', id: 'approve-redaksi-modal');
    }

    public function confirmApproveRedaksi()
    {
        $this->record->update([
            'status' => 'pending_keuangan',
            'redaksi_approved_by' => auth()->id(),
            'redaksi_approved_at' => now(),
            'redaksi_notes' => $this->redaksi_notes,
        ]);

        Notification::make()
            ->title('Pengajuan Disetujui')
            ->body('Pengajuan telah diteruskan ke keuangan untuk final approval.')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'approve-redaksi-modal');
        $this->redaksi_notes = '';
    }

    public function rejectRedaksi()
    {
        if (!auth()->user()->hasRole(['redaksi', 'admin']) || $this->record->status !== 'pending_redaksi') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk melakukan tindakan ini.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('open-modal', id: 'reject-redaksi-modal');
    }

    public function confirmRejectRedaksi()
    {
        if (empty($this->redaksi_notes)) {
            Notification::make()
                ->title('Alasan Diperlukan')
                ->body('Mohon berikan alasan penolakan.')
                ->warning()
                ->send();
            return;
        }

        $this->record->update([
            'status' => 'rejected',
            'redaksi_approved_by' => auth()->id(),
            'redaksi_approved_at' => now(),
            'redaksi_notes' => $this->redaksi_notes,
        ]);

        Notification::make()
            ->title('Pengajuan Ditolak')
            ->body('Pengajuan telah ditolak oleh redaksi.')
            ->warning()
            ->send();

        $this->dispatch('close-modal', id: 'reject-redaksi-modal');
        $this->redaksi_notes = '';
    }

    public function approveKeuangan()
    {
        if (!auth()->user()->hasRole(['keuangan', 'direktur', 'admin']) || $this->record->status !== 'pending_keuangan') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk melakukan tindakan ini.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('open-modal', id: 'approve-keuangan-modal');
    }

    public function confirmApproveKeuangan()
    {
        // Update budget allocations
        foreach ($this->record->detail_items as $item) {
            if (isset($item['budget_subcategory_id'])) {
                $allocation = \App\Models\BudgetAllocation::where('budget_subcategory_id', $item['budget_subcategory_id'])
                    ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
                    ->first();

                if ($allocation) {
                    $allocation->increment('used_amount', $item['total_price']);
                }
            }
        }

        $this->record->update([
            'status' => 'approved',
            'keuangan_approved_by' => auth()->id(),
            'keuangan_approved_at' => now(),
            'keuangan_notes' => $this->keuangan_notes,
        ]);

        Notification::make()
            ->title('Pengajuan Final Approved!')
            ->body('Pengajuan telah disetujui dan budget dialokasikan.')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'approve-keuangan-modal');
        $this->keuangan_notes = '';
    }

    public function rejectKeuangan()
    {
        if (!auth()->user()->hasRole(['keuangan', 'direktur', 'admin']) || $this->record->status !== 'pending_keuangan') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk melakukan tindakan ini.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('open-modal', id: 'reject-keuangan-modal');
    }

    public function confirmRejectKeuangan()
    {
        if (empty($this->keuangan_notes)) {
            Notification::make()
                ->title('Alasan Diperlukan')
                ->body('Mohon berikan alasan penolakan.')
                ->warning()
                ->send();
            return;
        }

        $this->record->update([
            'status' => 'rejected',
            'keuangan_approved_by' => auth()->id(),
            'keuangan_approved_at' => now(),
            'keuangan_notes' => $this->keuangan_notes,
        ]);

        Notification::make()
            ->title('Pengajuan Ditolak')
            ->body('Pengajuan ditolak oleh keuangan/direktur.')
            ->warning()
            ->send();

        $this->dispatch('close-modal', id: 'reject-keuangan-modal');
        $this->keuangan_notes = '';
    }

    // Helper methods untuk blade template
    public function getStatusColor(): string
    {
        return match($this->record->status) {
            'draft' => 'gray',
            'pending_redaksi' => 'warning',
            'pending_keuangan' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->record->status) {
            'draft' => 'Draft',
            'pending_redaksi' => 'Pending Redaksi',
            'pending_keuangan' => 'Pending Keuangan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->record->status)
        };
    }

    public function isOverdue(): bool
    {
        return $this->record->tanggal_dibutuhkan && 
               $this->record->tanggal_dibutuhkan->isPast() && 
               $this->record->status !== 'approved';
    }

    public function getDaysUntilNeeded(): int
    {
        if (!$this->record->tanggal_dibutuhkan) return 0;
        
        return now()->diffInDays($this->record->tanggal_dibutuhkan, false);
    }

    public function canUserTakeAction(): bool
    {
        $user = auth()->user();
        
        return match($this->record->status) {
            'pending_redaksi' => $user->hasRole(['redaksi', 'admin']),
            'pending_keuangan' => $user->hasRole(['keuangan', 'direktur', 'admin']),
            default => false
        };
    }
}