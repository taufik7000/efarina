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
        $this->logHistory($transaksi, 'DIBUAT');
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

        if (count($changes) === 1 && isset($changes['status'])) {
            $oldStatus = $transaksi->getOriginal('status');
            $newStatus = $transaksi->status;
            $this->logHistory($transaksi, "STATUS DIUBAH: Dari '{$oldStatus}' menjadi '{$newStatus}'");
        } else {
             $this->logHistory($transaksi, 'DIPERBARUI');
        }
    }

    /**
     * Handle the Transaksi "deleted" event.
     */
    public function deleted(Transaksi $transaksi): void
    {
        $this->logHistory($transaksi, 'DIHAPUS');
    }

    /**
     * Helper function to log history.
     */
    protected function logHistory(Transaksi $transaksi, string $action): void
    {
        TransaksiHistory::create([
            'transaksi_id' => $transaksi->id,
            'user_id' => Auth::id() ?? null,
            'action' => $action, // Membutuhkan kolom 'action'
            'keterangan' => 'Aksi dilakukan oleh ' . (Auth::user()->name ?? 'Sistem'), // Membutuhkan kolom 'keterangan'
        ]);
    }
}