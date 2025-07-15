<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kehadiran;
use Carbon\Carbon;

class KioskKehadiran extends Component
{
    public function render()
    {
        $kehadiranHariIni = Kehadiran::whereDate('tanggal', today('Asia/Jakarta'))
                                    ->with('pengguna:id,name')
                                    ->orderByRaw('CASE WHEN jam_pulang IS NOT NULL THEN jam_pulang ELSE jam_masuk END DESC')
                                    ->get();

        // Leaderboard untuk karyawan yang hadir tepat waktu (sebelum 08:00)
        $leaderboard = Kehadiran::whereDate('tanggal', today('Asia/Jakarta'))
                                ->with('pengguna:id,name')
                                ->whereTime('jam_masuk', '<', '22:15:00')
                                ->orderBy('jam_masuk', 'asc')
                                ->take(10) // Top 10 karyawan tercepat
                                ->get();

        // Statistik untuk gamifikasi
        $totalHadirTepatWaktu = $leaderboard->count();
        $totalHadirHariIni = $kehadiranHariIni->count();
        $persentaseTepatWaktu = $totalHadirHariIni > 0 ? round(($totalHadirTepatWaktu / $totalHadirHariIni) * 100) : 0;
                                    
        return view('livewire.kiosk-kehadiran', [
            'kehadiranHariIni' => $kehadiranHariIni,
            'leaderboard' => $leaderboard,
            'totalHadirTepatWaktu' => $totalHadirTepatWaktu,
            'totalHadirHariIni' => $totalHadirHariIni,
            'persentaseTepatWaktu' => $persentaseTepatWaktu
        ]);
    }
}