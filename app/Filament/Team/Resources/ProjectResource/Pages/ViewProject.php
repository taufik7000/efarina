<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.team.pages.view-project';

    // Properties untuk realisasi anggaran
    public $selectedPengajuan = null;
    public $selectedItemIndex = null;
    public $showRealizationModal = false;

    // Form data untuk realisasi
    public $tanggal_realisasi;
    public $vendor;
    public $qty_actual;
    public $harga_actual;
    public $total_actual;
    public $catatan_realisasi;
    public $bukti_files = [];

protected function getHeaderActions(): array
{
    return [
        Actions\EditAction::make()
            ->visible(fn () => $this->canEdit()),
    ];
}




    // ==================== PROJECT ACTIONS ====================

    public function approveAction()
    {
        $user = auth()->user();
        
        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'draft') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menyetujui project ini.')
                ->danger()
                ->send();
            return;
        }

        $this->record->update(['status' => 'planning']);
        
        Notification::make()
            ->title('Project Disetujui')
            ->body("Project '{$this->record->nama_project}' telah disetujui.")
            ->success()
            ->send();
    }

    public function rejectAction()
    {
        $user = auth()->user();
        
        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'draft') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menolak project ini.')
                ->danger()
                ->send();
            return;
        }

        $this->record->update(['status' => 'rejected']);
        
        Notification::make()
            ->title('Project Ditolak')
            ->body("Project '{$this->record->nama_project}' telah ditolak.")
            ->warning()
            ->send();
    }

    public function startProjectAction()
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'planning') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya redaksi yang dapat memulai project.')
                ->danger()
                ->send();
            return;
        }

        $this->record->update(['status' => 'in_progress']);

        Notification::make()
            ->title('Project Dimulai')
            ->body("Project '{$this->record->nama_project}' telah dimulai.")
            ->success()
            ->send();
    }

public function completeProjectAction()
{
    $user = auth()->user();
    
    // Cek permission - hanya redaksi
    if (!$user || !$user->hasRole(['redaksi']) || 
        !in_array($this->record->status, ['in_progress', 'review'])) {
        Notification::make()
            ->title('Akses Ditolak')
            ->body('Hanya redaksi yang dapat menyelesaikan project.')
            ->danger()
            ->send();
        return;
    }

    if (method_exists($this->record, 'markAsCompleted')) {
        $this->record->markAsCompleted();
    } else {
        $this->record->update([
            'status' => 'completed',
            'tanggal_selesai' => now(), // Set tanggal selesai
            'progress_percentage' => 100
        ]);
    }
    
    Notification::make()
        ->title('Project Selesai')
        ->body("Project '{$this->record->nama_project}' telah dinyatakan selesai oleh redaksi.")
        ->success()
        ->send();
}

    // ==================== REALISASI ANGGARAN ====================

    /**
     * Check apakah user bisa input realisasi
     */
    public function canInputRealization(): bool
    {
        return auth()->user()->id === $this->record->project_manager_id;
    }

    /**
     * Open modal untuk input realisasi
     */
    public function inputRealizationModal($pengajuanId, $itemIndex)
    {
        if (!$this->canInputRealization()) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya Project Manager yang dapat menginput realisasi anggaran.')
                ->danger()
                ->send();
            return;
        }

        $pengajuan = \App\Models\PengajuanAnggaran::find($pengajuanId);
        
        if (!$pengajuan || $pengajuan->status !== 'approved') {
            Notification::make()
                ->title('Tidak Dapat Direalisasi')
                ->body('Pengajuan anggaran ini tidak dapat direalisasi.')
                ->warning()
                ->send();
            return;
        }

        // Check jika item sudah direalisasi
        if (isset($pengajuan->detail_items[$itemIndex]['realisasi']) && 
            $pengajuan->detail_items[$itemIndex]['realisasi']['status'] === 'realized') {
            Notification::make()
                ->title('Sudah Direalisasi')
                ->body('Item ini sudah direalisasi sebelumnya.')
                ->warning()
                ->send();
            return;
        }

        $this->selectedPengajuan = $pengajuan;
        $this->selectedItemIndex = $itemIndex;
        
        // Get item data untuk pre-fill form
        $item = $pengajuan->detail_items[$itemIndex] ?? null;
        if ($item) {
            $this->tanggal_realisasi = now()->format('Y-m-d');
            $this->vendor = '';
            $this->qty_actual = $item['quantity'] ?? $item['kuantitas'] ?? 1;
            $this->harga_actual = $item['unit_price'] ?? $item['harga_satuan'] ?? 0;
            $this->total_actual = $item['total_price'] ?? 0;
            $this->catatan_realisasi = '';
            $this->bukti_files = [];
        }
        
        $this->showRealizationModal = true;
    }

    /**
     * Calculate total actual saat harga atau qty berubah
     */
    public function updatedQtyActual()
    {
        $this->calculateTotalActual();
    }

    public function updatedHargaActual()
    {
        $this->calculateTotalActual();
    }

    private function calculateTotalActual()
    {
        $this->total_actual = ($this->qty_actual ?? 0) * ($this->harga_actual ?? 0);
    }

    /**
     * Submit realisasi
     */
    public function submitRealization()
    {
        // Validation
        $this->validate([
            'tanggal_realisasi' => 'required|date',
            'vendor' => 'required|string|max:255',
            'qty_actual' => 'required|numeric|min:0',
            'harga_actual' => 'required|numeric|min:0',
            'total_actual' => 'required|numeric|min:0',
            'bukti_files' => 'required|array|min:1',
            'bukti_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ], [
            'bukti_files.required' => 'Minimal upload 1 bukti transaksi',
            'bukti_files.*.mimes' => 'File harus berformat JPG, PNG, atau PDF',
            'bukti_files.*.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            \DB::transaction(function () {
                // Upload files
                $uploadedFiles = [];
                foreach ($this->bukti_files as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs(
                        "bukti-realisasi/project-{$this->record->id}/pengajuan-{$this->selectedPengajuan->id}",
                        $filename,
                        'local' // PENTING: gunakan 'local' bukan 'public'
                    );

                    $uploadedFiles[] = [
                        'path' => $path,
                        'filename' => $filename, // Simpan filename terpisah untuk URL
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toISOString(),
                    ];
                }

                // Update detail_items
                $items = $this->selectedPengajuan->detail_items;
                $items[$this->selectedItemIndex]['realisasi'] = [
                    'tanggal_realisasi' => $this->tanggal_realisasi,
                    'vendor' => $this->vendor,
                    'qty_actual' => $this->qty_actual,
                    'harga_actual' => $this->harga_actual,
                    'total_actual' => $this->total_actual,
                    'catatan' => $this->catatan_realisasi,
                    'bukti_files' => $uploadedFiles,
                    'realized_by' => auth()->id(),
                    'realized_at' => now()->toISOString(),
                    'status' => 'realized'
                ];

                // Calculate total realisasi
                $totalRealized = collect($items)->sum(function ($item) {
                    return $item['realisasi']['total_actual'] ?? 0;
                });

                // Update pengajuan anggaran
                $this->selectedPengajuan->update([
                    'detail_items' => $items,
                    'realisasi_anggaran' => $totalRealized
                ]);
            });

            Notification::make()
                ->title('Realisasi Berhasil')
                ->body('Data realisasi anggaran telah disimpan.')
                ->success()
                ->send();

            $this->closeRealizationModal();
            $this->refreshData();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Gagal menyimpan realisasi: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Close modal dan reset form
     */
    public function closeRealizationModal()
    {
        $this->showRealizationModal = false;
        $this->selectedPengajuan = null;
        $this->selectedItemIndex = null;
        
        // Reset form data
        $this->tanggal_realisasi = null;
        $this->vendor = '';
        $this->qty_actual = null;
        $this->harga_actual = null;
        $this->total_actual = null;
        $this->catatan_realisasi = '';
        $this->bukti_files = [];
    }

    /**
     * Refresh data setelah update
     */
    private function refreshData()
    {
        $this->record = $this->record->fresh();
    }

    // ==================== PERMISSION HELPERS ====================

    public function canApprove(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'draft';
    }

    public function canReject(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'draft';
    }

    public function canStartProject(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'planning';
    }

   public function canCompleteProject(): bool
{
    $user = auth()->user();
    
    // Hanya redaksi yang bisa menyelesaikan project
    return $user && $user->hasRole(['redaksi']) && 
           in_array($this->record->status, ['in_progress', 'review']);
}

    public function canEdit(): bool
    {
    $user = auth()->user();
    
    // Hanya redaksi yang bisa edit project
    return $user && $user->hasRole(['redaksi']);
    }

    public function canView(): bool
{
    $user = auth()->user();
    
    if (!$user) {
        return false;
    }
    
    // Role yang bisa view semua project
    if ($user->hasRole(['admin', 'redaksi', 'direktur', 'keuangan', 'hrd'])) {
        return true;
    }
    
    // Team role bisa view project mereka sendiri
    if ($user->hasRole('team')) {
        return $user->id === $this->record->created_by ||
               $user->id === $this->record->project_manager_id ||
               (is_array($this->record->team_members) && in_array($user->id, $this->record->team_members));
    }
    
    return false;
}

public function canViewTasks(): bool
{
    $user = auth()->user();
    
    if (!$user) {
        return false;
    }
    
    // Admin dan redaksi bisa view tasks semua project
    if ($user->hasRole(['admin', 'redaksi'])) {
        return true;
    }
    
    // Team bisa view tasks project mereka
    if ($user->hasRole('team')) {
        return $user->id === $this->record->created_by ||
               $user->id === $this->record->project_manager_id ||
               (is_array($this->record->team_members) && in_array($user->id, $this->record->team_members));
    }
    
    return false;
}

public function canCreateTask(): bool
{
    $user = auth()->user();
    
    if (!$user) {
        return false;
    }
    
    // 1. Project harus dalam status yang diizinkan
    if (!in_array($this->record->status, ['in_progress', 'review'])) {
        return false;
    }
    
    // 2. User harus bisa create task secara umum
    if (!$user->can('create', \App\Models\Task::class)) {
        return false;
    }
    
    // 3. User harus terkait dengan project
    if ($user->hasRole(['admin', 'redaksi'])) {
        return true;
    }
    
    if ($user->hasRole('team')) {
        return $user->id === $this->record->created_by ||
               $user->id === $this->record->project_manager_id ||
               (is_array($this->record->team_members) && in_array($user->id, $this->record->team_members));
    }
    
    return false;
}

public function getCannotCreateTaskReason(): string
{
    $user = auth()->user();
    
    if (!$user) {
        return 'Anda harus login terlebih dahulu.';
    }
    
    // Check project status
    switch ($this->record->status) {
        case 'draft':
            return 'Project masih dalam status draft, belum bisa menambah task.';
        case 'planning':
            return 'Project masih dalam tahap planning, belum bisa menambah task.';
        case 'completed':
            return 'Project sudah selesai, tidak bisa menambah task baru.';
        case 'cancelled':
            return 'Project sudah dibatalkan, tidak bisa menambah task.';
    }
    
    // Check user permission
    if (!$user->can('create', \App\Models\Task::class)) {
        return 'Anda tidak memiliki izin untuk membuat task.';
    }
    
    // Check relation to project
    if (!($user->id === $this->record->created_by ||
          $user->id === $this->record->project_manager_id ||
          (is_array($this->record->team_members) && in_array($user->id, $this->record->team_members)))) {
        return 'Anda tidak terkait dengan project ini.';
    }
    
    return 'Tidak bisa menambah task saat ini.';
}

}