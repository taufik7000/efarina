<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Transaksi;

class ProjectObserver
{
    public function created(Project $project): void
    {
        // Project dibuat sebagai draft, tidak perlu auto-create transaksi
        // Transaksi akan dibuat manual saat diperlukan
    }

    public function updated(Project $project): void
    {
        // Bisa ditambahkan logic lain jika diperlukan
        // Misalnya update progress atau notifikasi status change
    }

    // Bisa ditambahkan method helper lain jika diperlukan
}