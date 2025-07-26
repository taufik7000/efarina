<?php

namespace App\Observers;

use App\Models\Transaksi;
use App\Models\TransaksiHistory;
use Illuminate\Support\Facades\Auth;

class TransaksiObserver
{
    /**
     * Handle the Transaksi "created" event.
     */
    public function created(Transaksi $transaksi): void
    {
        $this->logHistory(
            transaksi: $transaksi,
            statusFrom: null,
            statusTo: $transaksi->status,
            action: 'DIBUAT',
            notes: "Transaksi baru dibuat dengan nomor {$transaksi->nomor_transaksi}. " .
                   "Jenis: {$this->getJenisText($transaksi->jenis_transaksi)}, " .
                   "Total: " . number_format($transaksi->total_amount, 0, ',', '.') . ", " .
                   "Status awal: {$this->getStatusText($transaksi->status)}"
        );
    }

    /**
     * Handle the Transaksi "updated" event.
     */
    public function updated(Transaksi $transaksi): void
    {
        $changes = $transaksi->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        // Handle status change specifically
        if (count($changes) === 1 && isset($changes['status'])) {
            $oldStatus = $transaksi->getOriginal('status');
            $newStatus = $transaksi->status;
            
            $this->logHistory(
                transaksi: $transaksi,
                statusFrom: $oldStatus,
                statusTo: $newStatus,
                action: 'STATUS_CHANGED',
                notes: $this->generateStatusChangeNotes($oldStatus, $newStatus, $transaksi)
            );
        } 
        // Handle payment completion
        elseif (isset($changes['status']) && $transaksi->status === 'completed') {
            $this->logHistory(
                transaksi: $transaksi,
                statusFrom: $transaksi->getOriginal('status'),
                statusTo: $transaksi->status,
                action: 'PAYMENT_COMPLETED',
                notes: $this->generatePaymentCompletionNotes($transaksi, $changes)
            );
        }
        // Handle multiple field updates
        else {
            $this->logHistory(
                transaksi: $transaksi,
                statusFrom: $transaksi->getOriginal('status'),
                statusTo: $transaksi->status,
                action: 'UPDATED',
                notes: $this->generateUpdateNotes($changes, $transaksi)
            );
        }
    }

    /**
     * Handle the Transaksi "deleted" event.
     */
    public function deleted(Transaksi $transaksi): void
    {
        $this->logHistory(
            transaksi: $transaksi,
            statusFrom: $transaksi->status,
            statusTo: null,
            action: 'DELETED',
            notes: "Transaksi {$transaksi->nomor_transaksi} dihapus. " .
                   "Status terakhir: {$this->getStatusText($transaksi->status)}, " .
                   "Total: " . number_format($transaksi->total_amount, 0, ',', '.')
        );
    }

    /**
     * Helper function to log history with comprehensive information.
     */
    protected function logHistory(
        Transaksi $transaksi, 
        ?string $statusFrom, 
        ?string $statusTo, 
        string $action, 
        string $notes
    ): void {
        TransaksiHistory::create([
            'transaksi_id' => $transaksi->id,
            'user_id' => Auth::id() ?? null,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'action_by' => Auth::id() ?? null,
            'action_at' => now(),
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Generate detailed notes for status changes.
     */
    private function generateStatusChangeNotes(string $oldStatus, string $newStatus, Transaksi $transaksi): string
    {
        $userName = Auth::user()->name ?? 'Sistem';
        $statusFromText = $this->getStatusText($oldStatus);
        $statusToText = $this->getStatusText($newStatus);
        
        $notes = "Status transaksi diubah dari '{$statusFromText}' menjadi '{$statusToText}' oleh {$userName}";
        
        // Add context based on status transition
        switch ($newStatus) {
            case 'pending':
                $notes .= ". Transaksi menunggu persetujuan dari atasan.";
                break;
            case 'approved':
                $notes .= ". Transaksi telah disetujui dan menunggu pembayaran.";
                if ($transaksi->approved_by) {
                    $approver = \App\Models\User::find($transaksi->approved_by);
                    $notes .= " Disetujui oleh: " . ($approver->name ?? 'Unknown');
                }
                break;
            case 'completed':
                $notes .= ". Transaksi telah selesai dan pembayaran dikonfirmasi.";
                break;
            case 'rejected':
                $notes .= ". Transaksi ditolak.";
                if ($transaksi->catatan_approval) {
                    $notes .= " Alasan: " . $transaksi->catatan_approval;
                }
                break;
        }
        
        return $notes;
    }

    /**
     * Generate notes for payment completion.
     */
    private function generatePaymentCompletionNotes(Transaksi $transaksi, array $changes): string
    {
        $userName = Auth::user()->name ?? 'Sistem';
        $notes = "Pembayaran dikonfirmasi selesai oleh {$userName}";
        
        if (isset($changes['metode_pembayaran'])) {
            $metodeText = $this->getMetodePembayaranText($transaksi->metode_pembayaran);
            $notes .= ". Metode pembayaran: {$metodeText}";
        }
        
        if ($transaksi->nomor_referensi) {
            $notes .= ". No. Referensi: {$transaksi->nomor_referensi}";
        }
        
        // Check if new attachments were added
        if (isset($changes['attachments'])) {
            $attachments = $transaksi->attachments ?? [];
            $buktiCount = collect($attachments)->where('type', 'bukti_pembayaran')->count();
            if ($buktiCount > 0) {
                $notes .= ". Bukti pembayaran telah diupload ({$buktiCount} file)";
            }
        }
        
        return $notes;
    }

    /**
     * Generate notes for general updates.
     */
    private function generateUpdateNotes(array $changes, Transaksi $transaksi): string
    {
        $userName = Auth::user()->name ?? 'Sistem';
        $changedFields = [];
        
        foreach ($changes as $field => $newValue) {
            switch ($field) {
                case 'nama_transaksi':
                    $changedFields[] = "nama transaksi";
                    break;
                case 'total_amount':
                    $changedFields[] = "total amount";
                    break;
                case 'deskripsi':
                    $changedFields[] = "deskripsi";
                    break;
                case 'metode_pembayaran':
                    $changedFields[] = "metode pembayaran";
                    break;
                case 'nomor_referensi':
                    $changedFields[] = "nomor referensi";
                    break;
                case 'attachments':
                    $changedFields[] = "lampiran";
                    break;
                case 'catatan_approval':
                    $changedFields[] = "catatan approval";
                    break;
                default:
                    $changedFields[] = str_replace('_', ' ', $field);
            }
        }
        
        $fieldList = implode(', ', $changedFields);
        $notes = "Transaksi diperbarui oleh {$userName}. Field yang diubah: {$fieldList}";
        
        // Add specific details for important changes
        if (isset($changes['total_amount'])) {
            $notes .= ". Total baru: " . number_format($transaksi->total_amount, 0, ',', '.');
        }
        
        return $notes;
    }

    /**
     * Get human-readable status text.
     */
    private function getStatusText(?string $status): string
    {
        return match($status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Approval',
            'approved' => 'Menunggu Pembayaran',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            default => $status ?? 'Unknown'
        };
    }

    /**
     * Get human-readable jenis transaksi text.
     */
    private function getJenisText(?string $jenis): string
    {
        return match($jenis) {
            'pemasukan' => 'Pemasukan',
            'pengeluaran' => 'Pengeluaran',
            default => $jenis ?? 'Unknown'
        };
    }

    /**
     * Get human-readable metode pembayaran text.
     */
    private function getMetodePembayaranText(?string $metode): string
    {
        return match($metode) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
            'e_wallet' => 'E-Wallet',
            'cek' => 'Cek',
            default => $metode ?? 'Tidak diketahui'
        };
    }
}