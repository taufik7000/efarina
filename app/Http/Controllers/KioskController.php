<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KioskController extends Controller
{
    /**
     * Menampilkan halaman Kiosk utama.
     * Tidak ada data yang perlu dikirim karena daftar kehadiran dihapus.
     */
    public function tampilkanKiosk()
    {
        return view('kiosk.tampilan');
    }

    /**
     * Membuat QR code dinamis yang unik dan berbatas waktu.
     * Fungsi ini tidak berubah dan tetap menjadi inti dari Kiosk.
     */
    public function buatQrCode()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $token = Str::random(40); // Token unik untuk setiap QR code
            $expiredAt = $now->copy()->addSeconds(20); // Waktu valid 20 detik

            // Simpan token ke Cache dengan waktu kedaluwarsa
            Cache::put('qrcode_token', $token, $expiredAt);

            // Hasilkan gambar QR code dari token
            $qrCodeImage = QrCode::format('svg')->size(300)->generate($token);

            return response($qrCodeImage)->header('Content-Type', 'image/svg+xml');

        } catch (\Exception $e) {
            Log::error('Gagal membuat QR code', ['error' => $e->getMessage()]);
            // Jika gagal, buat QR code berisi pesan error agar mudah di-debug
            $errorQr = QrCode::format('svg')->size(300)->generate('ERROR: Gagal membuat QR Code');
            return response($errorQr)->header('Content-Type', 'image/svg+xml');
        }
    }

    /**
     * Memvalidasi token QR dan mencatat absensi karyawan.
     * Logika event untuk real-time update telah dihapus.
     */
    public function validasiAbsensi(Request $request)
    {
        try {
            $validatedData = $request->validate(['token' => 'required|string']);
            $tokenDariPonsel = $validatedData['token'];
            $tokenValidDiServer = Cache::get('qrcode_token');
            $now = Carbon::now('Asia/Jakarta');

            // 1. Validasi Token
            if (!$tokenValidDiServer || $tokenDariPonsel !== $tokenValidDiServer) {
                return response()->json(['pesan' => 'QR Code tidak valid atau kedaluwarsa.'], 422);
            }
            
            // 2. Ambil User dan hapus token agar tidak bisa dipakai lagi
            $pengguna = Auth::user();
            if (!$pengguna) {
                return response()->json(['pesan' => 'Sesi tidak ditemukan. Silakan login ulang.'], 401);
            }
            Cache::forget('qrcode_token');

            // 3. Cek catatan kehadiran hari ini
            $hariIni = $now->toDateString();
            $kehadiranHariIni = Kehadiran::where('user_id', $pengguna->id)
                                         ->whereDate('tanggal', $hariIni)
                                         ->first();

            if (!$kehadiranHariIni) {
                // Proses Absen Masuk
                $jamMasuk = $now->toTimeString();
                Kehadiran::create([
                    'user_id' => $pengguna->id,
                    'tanggal' => $hariIni,
                    'jam_masuk' => $jamMasuk,
                    'status' => $this->tentukanStatusKehadiran($now),
                    'metode_absen' => 'qrcode',
                ]);
                return response()->json(['status' => 'success', 'pesan' => "Absen Masuk Berhasil pada {$jamMasuk}!"]);
            }
            
            if (is_null($kehadiranHariIni->jam_pulang)) {
                // Proses Absen Pulang
                $jamPulang = $now->toTimeString();
                $kehadiranHariIni->update(['jam_pulang' => $jamPulang]);
                return response()->json(['status' => 'success', 'pesan' => "Absen Pulang Berhasil pada {$jamPulang}!"]);
            }
            
            // Jika sudah absen masuk dan pulang
            return response()->json(['pesan' => 'Anda sudah melakukan absen masuk dan pulang hari ini.'], 422);

        } catch (\Exception $e) {
            Log::error('Error saat validasi absensi', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return response()->json(['pesan' => 'Terjadi kesalahan internal pada server.'], 500);
        }
    }
    
    /**
     * Tentukan status kehadiran berdasarkan jam masuk.
     * Fungsi helper ini tetap diperlukan untuk logika absensi.
     */
    private function tentukanStatusKehadiran(Carbon $jamMasuk): string
    {
        // Jam masuk kantor ditetapkan pukul 08:00
        $jamKerja = Carbon::createFromTime(8, 15, 0, 'Asia/Jakarta');

        if ($jamMasuk->lte($jamKerja)) {
            return 'Tepat Waktu';
        } else {
            // Jika lebih dari jam 08:00, dianggap Terlambat
            return 'Terlambat';
        }
    }
}