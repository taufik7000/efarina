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
        $actions = [];

        // Edit Action - hanya untuk creator saat status draft/rejected
        if (
            $this->record->created_by === auth()->id() &&
            in_array($this->record->status, ['draft', 'rejected'])
        ) {
            $actions[] = Actions\EditAction::make();
        }

        // REDAKSI ACTIONS - untuk status pending_redaksi
        if (
            $this->record->status === 'pending_redaksi' &&
            auth()->user()->hasRole(['redaksi', 'admin'])
        ) {

            $actions[] = Actions\Action::make('approve_redaksi')
                ->label('Setujui (Redaksi)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('redaksi_notes')
                        ->label('Catatan Redaksi')
                        ->placeholder('Tambahkan catatan (opsional)')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'pending_keuangan',
                        'redaksi_approved_by' => auth()->id(),
                        'redaksi_approved_at' => now(),
                        'redaksi_notes' => $data['redaksi_notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Pengajuan Disetujui Redaksi')
                        ->body('Pengajuan diteruskan ke keuangan untuk final approval.')
                        ->success()
                        ->send();
                });

            $actions[] = Actions\Action::make('reject_redaksi')
                ->label('Tolak (Redaksi)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('redaksi_notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'rejected',
                        'redaksi_approved_by' => auth()->id(),
                        'redaksi_approved_at' => now(),
                        'redaksi_notes' => $data['redaksi_notes'],
                    ]);

                    Notification::make()
                        ->title('Pengajuan Ditolak')
                        ->body('Pengajuan ditolak oleh redaksi.')
                        ->warning()
                        ->send();
                });
        }

        // KEUANGAN ACTIONS - untuk status pending_keuangan
        if (
            $this->record->status === 'pending_keuangan' &&
            auth()->user()->hasRole(['keuangan', 'direktur', 'admin'])
        ) {

            $actions[] =


                // Ganti method approve_keuangan di header actions (ViewPengajuanAnggaran.php)
Actions\Action::make('approve_keuangan')
    ->label('Setujui (Final)')
    ->icon('heroicon-o-check-badge')
    ->color('success')
    ->form([
        Forms\Components\Textarea::make('keuangan_notes')
            ->label('Catatan Keuangan')
            ->placeholder('Tambahkan catatan (opsional)')
            ->rows(3),
    ])
    ->action(function (array $data): void {
        \DB::transaction(function () use ($data) {
            // 1. Update status pengajuan
            $this->record->update([
                'status' => 'approved',
                'keuangan_approved_by' => auth()->id(),
                'keuangan_approved_at' => now(),
                'keuangan_notes' => $data['keuangan_notes'] ?? null,
            ]);

            // 2. Create transaksi pengeluaran
            $transaksi = \App\Models\Transaksi::create([
                'nomor_transaksi' => self::generateNomorTransaksi(),
                'jenis_transaksi' => 'pengeluaran',
                'tanggal_transaksi' => now(),
                'nama_transaksi' => 'Pengeluaran: ' . $this->record->judul_pengajuan,
                'deskripsi' => $this->record->deskripsi,
                'total_amount' => $this->record->total_anggaran,
                'status' => 'approved',
                'metode_pembayaran' => 'transfer',
                'project_id' => $this->record->project_id,
                'pengajuan_anggaran_id' => $this->record->id,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'workflow_type' => 'pengajuan_anggaran',
                'catatan_approval' => 'Disetujui melalui pengajuan anggaran: ' . $this->record->nomor_pengajuan,
            ]);

            // 3. Create transaksi items
            foreach ($this->record->detail_items as $item) {
                \App\Models\TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'nama_item' => $item['item_name'] ?? $item['nama_item'] ?? 'Item',
                    'kuantitas' => $item['quantity'] ?? $item['kuantitas'] ?? 1,
                    'harga_satuan' => $item['unit_price'] ?? $item['harga_satuan'] ?? 0,
                    'subtotal' => $item['total_price'] ?? 0,
                    'satuan' => 'pcs',
                    'deskripsi_item' => $item['description'] ?? $item['spesifikasi'] ?? null,
                ]);
            }

            // 4. Update budget allocations
            foreach ($this->record->detail_items as $item) {
                if (isset($item['budget_subcategory_id'])) {
                    $allocation = \App\Models\BudgetAllocation::where('budget_subcategory_id', $item['budget_subcategory_id'])
                        ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
                        ->first();

                    if ($allocation) {
                        $allocation->increment('used_amount', $item['total_price']);
                        
                        if (!$transaksi->budget_allocation_id) {
                            $transaksi->update(['budget_allocation_id' => $allocation->id]);
                        }
                    }
                }
            }
        });

        Notification::make()
            ->title('Pengajuan Final Approved!')
            ->body("Pengajuan '{$this->record->judul_pengajuan}' telah disetujui, budget dialokasikan, dan transaksi pengeluaran dibuat.")
            ->success()
            ->send();
    });

            $actions[] = Actions\Action::make('reject_keuangan')
                ->label('Tolak (Final)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('keuangan_notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'rejected',
                        'keuangan_approved_by' => auth()->id(),
                        'keuangan_approved_at' => now(),
                        'keuangan_notes' => $data['keuangan_notes'],
                    ]);

                    Notification::make()
                        ->title('Pengajuan Ditolak')
                        ->body('Pengajuan ditolak oleh keuangan/direktur.')
                        ->warning()
                        ->send();
                });
        }

        // Submit Action - untuk team saat status draft
        if (
            $this->record->status === 'draft' &&
            $this->record->created_by === auth()->id()
        ) {

            $actions[] = Actions\Action::make('submit')
                ->label('Ajukan ke Redaksi')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Ajukan ke Redaksi')
                ->modalDescription('Setelah diajukan, pengajuan tidak bisa diubah dan akan masuk ke workflow approval.')
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'pending_redaksi',
                        'tanggal_pengajuan' => now(),
                    ]);

                    Notification::make()
                        ->title('Pengajuan Terkirim')
                        ->body("Pengajuan '{$this->record->judul_pengajuan}' telah dikirim ke redaksi untuk review.")
                        ->success()
                        ->send();
                });
        }


        return $actions;
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
    \DB::transaction(function () {
        // 1. Update status pengajuan
        $this->record->update([
            'status' => 'approved',
            'keuangan_approved_by' => auth()->id(),
            'keuangan_approved_at' => now(),
            'keuangan_notes' => $this->keuangan_notes,
        ]);

        // 2. Create transaksi pengeluaran
        $transaksi = \App\Models\Transaksi::create([
            'nomor_transaksi' => self::generateNomorTransaksi(),
            'jenis_transaksi' => 'pengeluaran',
            'tanggal_transaksi' => now(),
            'nama_transaksi' => 'Pengeluaran: ' . $this->record->judul_pengajuan,
            'deskripsi' => $this->record->deskripsi,
            'total_amount' => $this->record->total_anggaran,
            'status' => 'approved',
            'metode_pembayaran' => 'transfer',
            'project_id' => $this->record->project_id,
            'pengajuan_anggaran_id' => $this->record->id,
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'workflow_type' => 'pengajuan_anggaran',
            'catatan_approval' => 'Disetujui melalui pengajuan anggaran: ' . $this->record->nomor_pengajuan,
        ]);

        // 3. Create transaksi items
        foreach ($this->record->detail_items as $item) {
            \App\Models\TransaksiItem::create([
                'transaksi_id' => $transaksi->id,
                'nama_item' => $item['item_name'] ?? $item['nama_item'] ?? 'Item',
                'kuantitas' => $item['quantity'] ?? $item['kuantitas'] ?? 1,
                'harga_satuan' => $item['unit_price'] ?? $item['harga_satuan'] ?? 0,
                'subtotal' => $item['total_price'] ?? 0,
                'satuan' => 'pcs',
                'deskripsi_item' => $item['description'] ?? $item['spesifikasi'] ?? null,
            ]);
        }

        // 4. Update budget allocations
        foreach ($this->record->detail_items as $item) {
            if (isset($item['budget_subcategory_id'])) {
                $allocation = \App\Models\BudgetAllocation::where('budget_subcategory_id', $item['budget_subcategory_id'])
                    ->whereHas('budgetPlan', fn($q) => $q->where('status', 'active'))
                    ->first();

                if ($allocation) {
                    $allocation->increment('used_amount', $item['total_price']);
                    
                    if (!$transaksi->budget_allocation_id) {
                        $transaksi->update(['budget_allocation_id' => $allocation->id]);
                    }
                }
            }
        }
    });

    Notification::make()
        ->title('Pengajuan Final Approved!')
        ->body('Pengajuan telah disetujui, budget dialokasikan, dan transaksi pengeluaran dibuat.')
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
        return match ($this->record->status) {
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
        return match ($this->record->status) {
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
        if (!$this->record->tanggal_dibutuhkan)
            return 0;

        return now()->diffInDays($this->record->tanggal_dibutuhkan, false);
    }

    public function canUserTakeAction(): bool
    {
        $user = auth()->user();

        return match ($this->record->status) {
            'pending_redaksi' => $user->hasRole(['redaksi', 'admin']),
            'pending_keuangan' => $user->hasRole(['keuangan', 'direktur', 'admin']),
            default => false
        };
    }

private static function generateNomorTransaksi(): string
{
    $prefix = 'TRX-OUT';
    $year = now()->format('Y');
    $month = now()->format('m');
    
    $counter = \App\Models\Transaksi::whereYear('tanggal_transaksi', now()->year)
                     ->whereMonth('tanggal_transaksi', now()->month)
                     ->where('jenis_transaksi', 'pengeluaran')
                     ->count() + 1;
    
    return $prefix . '/' . $year . '/' . $month . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
}
}