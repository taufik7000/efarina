<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kehadiran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KehadiranJuliSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run(): void
    {
        $userId = 3;
        $tahun = 2025;
        $bulan = 7; // Juli

        // Pastikan user dengan ID 3 ada di database
        if (!User::find($userId)) {
            $this->command->error("User dengan ID {$userId} tidak ditemukan. Seeder dibatalkan.");
            return;
        }

        // Hapus data lama untuk user dan periode ini agar tidak duplikat
        Kehadiran::where('user_id', $userId)
                 ->whereYear('tanggal', $tahun)
                 ->whereMonth('tanggal', $bulan)
                 ->delete();

        $this->command->info("Memulai seeder untuk User ID: {$userId} pada bulan Juli 2025.");

        // Loop dari tanggal 1 sampai 13 Juli
        for ($hari = 1; $hari <= 13; $hari++) {
            $tanggal = Carbon::create($tahun, $bulan, $hari);

            // Lewati hari Minggu (dianggap hari libur)
            if ($tanggal->isSunday()) {
                $this->command->warn("SKIP: Tanggal {$tanggal->toDateString()} adalah hari Minggu (libur).");
                continue;
            }

            // --- Logika untuk Jam Masuk dan Pulang ---
            // Jam masuk acak antara 07:30 dan 09:30
            $jamMasuk = $tanggal->copy()->setTime(rand(7, 8), rand(30, 59), rand(0, 59));

            // Jam pulang acak antara 17:00 dan 18:00
            $jamPulang = $tanggal->copy()->setTime(rand(17, 18), rand(0, 59), rand(0, 59));
            
            // --- Logika untuk Status ---
            // Batas waktu adalah 08:15:00
            $batasTepatWaktu = $tanggal->copy()->setTime(8, 15, 0);
            $status = $jamMasuk->lte($batasTepatWaktu) ? 'Tepat Waktu' : 'Terlambat';
            
            Kehadiran::create([
                'user_id' => $userId,
                'tanggal' => $tanggal->toDateString(),
                'jam_masuk' => $jamMasuk->toTimeString(),
                'jam_pulang' => $jamPulang->toTimeString(),
                'status' => $status,
                'metode_absen' => 'qrcode_seeded', // Menandakan data ini dari seeder
            ]);

            $this->command->info("SUCCESS: Data dibuat untuk tanggal {$tanggal->toDateString()} [Status: {$status}]");
        }
        
        $this->command->info('Seeder data kehadiran Juli berhasil dijalankan!');
    }
}